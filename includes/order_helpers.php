<?php
/**
 * Shared rules for online pick-up orders and POS sales.
 * Prices and stock always come from the database, never from the browser.
 */

/** Thrown when stock changed between pricing and saving (another customer or cashier got there first). */
class StockChangedException extends Exception {}

/** How long after the chosen pick-up date a reservation is held before it expires. */
const PICKUP_HOLD_DAYS = 2;

/**
 * Re-prices a cart sent by the browser against the database.
 * $raw: list of ['type' => 'product'|'phone_unit', 'id' => int, 'quantity' => int]
 * $lock: SELECT ... FOR UPDATE (use inside a transaction when the stock is about to change)
 * Returns ['items' => [...], 'total' => float, 'errors' => [string, ...]]
 * Each item: type, id, name, unit_price, quantity, subtotal
 */
function price_cart(PDO $pdo, array $raw, bool $lock = false): array {
    $items = [];
    $errors = [];
    $merged = [];

    // Merge duplicate lines and drop junk
    foreach ($raw as $line) {
        if (!is_array($line)) continue;
        $type = ($line['type'] ?? '') === 'phone_unit' ? 'phone_unit' : (($line['type'] ?? '') === 'product' ? 'product' : null);
        $id = (int)($line['id'] ?? 0);
        $qty = (int)($line['quantity'] ?? 1);
        if (!$type || $id <= 0) continue;
        if ($type === 'phone_unit') $qty = 1;           // every phone is a single, IMEI-tracked unit
        if ($qty <= 0) continue;
        $key = $type . ':' . $id;
        $merged[$key] = ['type' => $type, 'id' => $id, 'quantity' => min(99, ($merged[$key]['quantity'] ?? 0) + $qty)];
        if ($type === 'phone_unit') $merged[$key]['quantity'] = 1;
    }

    $suffix = $lock ? ' FOR UPDATE' : '';
    $p_stmt = $pdo->prepare("SELECT id, name, selling_price, stock_quantity, status FROM products WHERE id = ?" . $suffix);
    $u_stmt = $pdo->prepare("SELECT id, brand, model_name, storage_capacity, color, selling_price, status FROM phone_units WHERE id = ?" . $suffix);

    foreach ($merged as $line) {
        if ($line['type'] === 'product') {
            $p_stmt->execute([$line['id']]);
            $p = $p_stmt->fetch();
            if (!$p || $p['status'] === 'archived') {
                $errors[] = "An item in your cart is no longer sold.";
                continue;
            }
            if ((int)$p['stock_quantity'] < $line['quantity']) {
                $errors[] = (int)$p['stock_quantity'] > 0
                    ? "Only {$p['stock_quantity']} left of “{$p['name']}”."
                    : "“{$p['name']}” is out of stock.";
                continue;
            }
            $price = (float)$p['selling_price'];
            $items[] = ['type' => 'product', 'id' => (int)$p['id'], 'name' => $p['name'], 'unit_price' => $price,
                        'quantity' => $line['quantity'], 'subtotal' => round($price * $line['quantity'], 2)];
        } else {
            $u_stmt->execute([$line['id']]);
            $u = $u_stmt->fetch();
            $name = $u ? trim("{$u['brand']} {$u['model_name']} {$u['storage_capacity']}" . ($u['color'] ? " ({$u['color']})" : '')) : 'A phone';
            if (!$u || $u['status'] !== 'in_stock') {
                $errors[] = "“{$name}” was just reserved or sold.";
                continue;
            }
            $price = (float)$u['selling_price'];
            $items[] = ['type' => 'phone_unit', 'id' => (int)$u['id'], 'name' => $name, 'unit_price' => $price,
                        'quantity' => 1, 'subtotal' => $price];
        }
    }

    $total = round(array_sum(array_column($items, 'subtotal')), 2);
    return ['items' => $items, 'total' => $total, 'errors' => $errors];
}

/**
 * Cancels (or expires) a pick-up order that has not been claimed, and puts reserved phones back on sale.
 * Returns true when the order was open and is now closed.
 */
function close_pickup_order(PDO $pdo, int $order_id, string $new_status = 'cancelled', string $note = ''): bool {
    $new_status = $new_status === 'expired' ? 'expired' : 'cancelled';
    $own_tx = !$pdo->inTransaction();
    if ($own_tx) $pdo->beginTransaction();
    try {
        $o = $pdo->prepare("SELECT id, order_code, notes FROM online_orders WHERE id = ? AND pickup_status IN ('pending_confirmation','ready_for_pickup') FOR UPDATE");
        $o->execute([$order_id]);
        $order = $o->fetch();
        if (!$order) {
            if ($own_tx) $pdo->rollBack();
            return false;
        }
        $notes = trim(($order['notes'] ?? '') . ($note !== '' ? "\n" . date('Y-m-d H:i') . " – " . $note : ''));
        $pdo->prepare("UPDATE online_orders SET pickup_status = ?, notes = ? WHERE id = ?")->execute([$new_status, $notes, $order_id]);
        // Release reserved phones (only if they are still held for this order)
        $pdo->prepare("UPDATE phone_units u
                       JOIN online_order_items i ON i.phone_unit_id = u.id AND i.order_id = ?
                       SET u.status = 'in_stock'
                       WHERE u.status = 'reserved'")->execute([$order_id]);
        if ($own_tx) $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($own_tx && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/** Expires reservations whose pick-up date passed more than PICKUP_HOLD_DAYS ago. Returns how many. */
function expire_overdue_pickups(PDO $pdo): int {
    try {
        $ids = $pdo->query("SELECT id FROM online_orders
                            WHERE pickup_status IN ('pending_confirmation','ready_for_pickup')
                              AND pickup_target_date < CURDATE() - INTERVAL " . PICKUP_HOLD_DAYS . " DAY")->fetchAll(PDO::FETCH_COLUMN);
        $n = 0;
        foreach ($ids as $id) {
            if (close_pickup_order($pdo, (int)$id, 'expired', 'Not collected within the ' . PICKUP_HOLD_DAYS . '-day hold; reserved items released')) $n++;
        }
        return $n;
    } catch (Exception $e) {
        return 0;
    }
}

/** Display label + Bootstrap classes for an online order status. */
function pickup_status_badge(string $status): array {
    switch ($status) {
        case 'claimed':          return ['Claimed', 'bg-dark text-white', 'fa-check'];
        case 'ready_for_pickup': return ['Ready for pick-up', 'bg-success-subtle text-success border border-success-subtle', 'fa-bell'];
        case 'cancelled':        return ['Cancelled', 'bg-danger-subtle text-danger border border-danger-subtle', 'fa-ban'];
        case 'expired':          return ['Expired', 'bg-secondary-subtle text-secondary border', 'fa-hourglass-end'];
        default:                 return ['Being prepared', 'bg-light text-secondary border', 'fa-clock'];
    }
}
