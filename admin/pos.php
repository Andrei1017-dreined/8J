<?php
$page_title = "POS & Billing Cashier";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/order_helpers.php';
require_role('admin', 'staff');

$message = '';
$sale_success = null;

// Fetch accessories with category details
$products = $pdo->query("
    SELECT p.id, p.name, p.brand, p.selling_price, p.stock_quantity, p.sku, p.category_id, p.image,
           c.name as category_name
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock_quantity > 0 AND p.status = 'available'
    ORDER BY p.name ASC
")->fetchAll();

// Fetch available phone units (IMEI tracked)
$phone_units = $pdo->query("
    SELECT id, brand, model_name, storage_capacity, color, selling_price, imei_1, unit_condition, battery_health 
    FROM phone_units 
    WHERE status = 'in_stock' 
    ORDER BY brand ASC, model_name ASC
")->fetchAll();

// Fetch distinct categories actually used by available products
$categories = $pdo->query("
    SELECT DISTINCT c.id, c.name 
    FROM categories c
    JOIN products p ON p.category_id = c.id
    WHERE p.stock_quantity > 0 AND p.status = 'available'
    ORDER BY c.name ASC
")->fetchAll();

// Fetch customer accounts for quick customer picker
// (was LIMIT 30, which hid every customer after the 30th name alphabetically)
$customer_list = $pdo->query("SELECT id, fullname, contact_number FROM users WHERE role = 'customer' AND status = 'active' ORDER BY fullname ASC LIMIT 1000")->fetchAll();

// Handle Checkout Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pos_checkout') {
    $customer_name = sanitize($_POST['customer_name'] ?? '') ?: 'Walk-in Customer';
    $customer_id = (int)($_POST['customer_id'] ?? 0) ?: null;
    $discount_req = max(0, (float)($_POST['discount'] ?? 0));
    $amount_tendered = (float)($_POST['amount_tendered'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'cash';
    if (!in_array($payment_method, ['cash', 'gcash', 'card', 'split'], true)) $payment_method = 'cash';
    $payment_ref = mb_substr(sanitize($_POST['payment_reference'] ?? ''), 0, 80);
    $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
    $cart_items = is_array($cart_items) ? $cart_items : [];

    if (empty($cart_items)) {
        $message = "Error: Cart cannot be empty!";
    } else {
        try {
            $pdo->beginTransaction();

            // Re-price from the database and lock the rows, so stock can't be oversold
            // and the same phone can't be sold twice by two cashiers at once
            $priced = price_cart($pdo, $cart_items, true);
            if ($priced['errors'] || !$priced['items']) {
                throw new StockChangedException(implode(' ', array_unique($priced['errors'])) ?: 'The bill is empty.');
            }
            $subtotal = $priced['total'];
            $discount = round(min($discount_req, $subtotal), 2);
            $total_amount = round($subtotal - $discount, 2);

            // Card / GCash are charged the exact amount; only cash gives change
            if ($payment_method !== 'cash' && $amount_tendered <= 0) $amount_tendered = $total_amount;
            if ($amount_tendered + 0.009 < $total_amount) {
                throw new StockChangedException("Amount tendered (" . format_currency($amount_tendered) . ") is less than the total due (" . format_currency($total_amount) . ").");
            }
            $cart_items = array_map(fn($i) => ['type' => $i['type'], 'id' => $i['id'], 'name' => $i['name'], 'price' => $i['unit_price'], 'quantity' => $i['quantity']], $priced['items']);

            $change_due = $payment_method === 'cash' ? round(max(0, $amount_tendered - $total_amount), 2) : 0.0;
            $invoice_no = '8J-INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 1. Insert into sales
            $cashier_id = !empty($current_user['id']) ? $current_user['id'] : ($_SESSION['user']['id'] ?? 1);
            $stmt = $pdo->prepare("
                INSERT INTO sales 
                (invoice_no, customer_id, customer_name, subtotal, discount_amount, total_amount, amount_tendered, change_due, payment_method, payment_reference, cashier_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $invoice_no, $customer_id, $customer_name, $subtotal, $discount, $total_amount, 
                $amount_tendered, $change_due, $payment_method, $payment_ref, $cashier_id
            ]);
            $sale_id = $pdo->lastInsertId();

            // 2. Insert items and update stock / status
            $item_stmt = $pdo->prepare("
                INSERT INTO sale_items 
                (sale_id, item_type, product_id, phone_unit_id, item_name, unit_price, quantity, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $items_summary = [];
            $upd_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
            $upd_phone = $pdo->prepare("UPDATE phone_units SET status = 'sold' WHERE id = ? AND status = 'in_stock'");
            foreach ($cart_items as $item) {
                if ($item['type'] === 'product') {
                    $item_stmt->execute([$sale_id, 'product', $item['id'], null, $item['name'], $item['price'], $item['quantity'], round($item['price'] * $item['quantity'], 2)]);
                    $upd_stock->execute([$item['quantity'], $item['id'], $item['quantity']]);
                    if ($upd_stock->rowCount() !== 1) throw new StockChangedException("Not enough stock left for {$item['name']}.");
                    $items_summary[] = "{$item['quantity']}x {$item['name']}";
                } elseif ($item['type'] === 'phone_unit') {
                    $item_stmt->execute([$sale_id, 'phone_unit', null, $item['id'], $item['name'], $item['price'], 1, $item['price']]);
                    $upd_phone->execute([$item['id']]);
                    if ($upd_phone->rowCount() !== 1) throw new StockChangedException("{$item['name']} was already sold or reserved.");
                    $items_summary[] = "1x Phone Unit ({$item['name']})";
                }
            }
            if ($discount > 0) {
                $items_summary[] = "discount " . format_currency($discount);
            }

            audit_log('POS Sale Completed', 'POS', "Invoice {$invoice_no}: Total ₱" . number_format($total_amount, 2) . " via " . strtoupper($payment_method) . " (" . implode(', ', array_slice($items_summary, 0, 3)) . ")");

            $pdo->commit();
            $sale_success = [
                'invoice_no' => $invoice_no,
                'customer_name' => $customer_name,
                'total_amount' => $total_amount,
                'amount_tendered' => $amount_tendered,
                'change_due' => $change_due,
                'payment_method' => $payment_method,
                'items' => $cart_items,
                'date' => date('Y-m-d H:i:s')
            ];
            $message = "Transaction completed successfully! Invoice #" . $invoice_no;

            // Refresh products & phone units list to reflect real-time deducted stock
            $products = $pdo->query("
                SELECT p.id, p.name, p.brand, p.selling_price, p.stock_quantity, p.sku, p.category_id, p.image,
                       c.name as category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.stock_quantity > 0 AND p.status = 'available'
                ORDER BY p.name ASC
            ")->fetchAll();

            $phone_units = $pdo->query("
                SELECT id, brand, model_name, storage_capacity, color, selling_price, imei_1, unit_condition, battery_health 
                FROM phone_units 
                WHERE status = 'in_stock' 
                ORDER BY brand ASC, model_name ASC
            ")->fetchAll();

        } catch (StockChangedException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Error: " . $e->getMessage() . " Nothing was charged. Please refresh the bill and try again.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('POS checkout error: ' . $e->getMessage());
            $message = "Error: The sale could not be saved. Nothing was charged. Please try again.";
        }
    }
}

// Fetch 5 most recent sales today for quick reprint drawer
$today_sales = $pdo->query("SELECT * FROM sales WHERE DATE(created_at) = CURDATE() ORDER BY id DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ========================================================= */
/* POS MODERN SPACIOUS LAYOUT & RESPONSIVE POLISH             */
/* ========================================================= */

/* Category Filter Chips */
.pos-cat-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-top: 10px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.pos-cat-pill {
    white-space: nowrap;
    border-radius: 50px;
    padding: 7px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    flex-shrink: 0;
}
.pos-cat-pill:hover {
    background: #fdf2f8;
    color: #db2777;
    border-color: #f472b6;
}
.pos-cat-pill.active {
    background: #db2777;
    color: #ffffff;
    border-color: #db2777;
    box-shadow: 0 4px 12px rgba(219, 39, 119, 0.3);
}

/* Spacious Item Cards */
.pos-card-item {
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 12px;
    background: #ffffff;
    user-select: none;
    border: 1px solid #e2e8f0;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 16px;
}
.pos-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(0,0,0,0.08);
    border-color: #f472b6 !important;
}
.pos-card-item:active {
    transform: scale(0.98);
}
.pos-card-title {
    font-size: 0.94rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 2.6em;
}
.pos-card-price {
    font-size: 1.12rem;
    font-weight: 800;
    color: #0f172a;
}
.pos-add-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fdf2f8;
    color: #db2777;
    border: 1px solid #fbcfe8;
    transition: all 0.15s ease;
    font-size: 12px;
}
.pos-card-item:hover .pos-add-btn {
    background: #db2777;
    color: #ffffff;
    border-color: #db2777;
}

/* Right-Side Cart Panel */
.pos-cart-panel {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    padding: 22px;
}
.pos-cart-list {
    max-height: 320px;
    overflow-y: auto;
    padding-right: 6px;
}
.pos-cart-row {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.pos-qty-pill-group {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    padding: 2px 6px;
}
.pos-qty-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 11px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    cursor: pointer;
    transition: all 0.15s ease;
}
.pos-qty-btn:hover {
    background: #db2777;
    color: #ffffff;
    border-color: #db2777;
}

/* Bill Summary Rows */
.bill-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.92rem;
    margin-bottom: 8px;
}
.bill-summary-row.total-due {
    font-size: 1.45rem;
    font-weight: 800;
    color: #db2777;
    border-top: 2px dashed #cbd5e1;
    padding-top: 12px;
    margin-top: 12px;
}

/* Denomination Chips */
.quick-cash-pill {
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    transition: all 0.15s ease;
    user-select: none;
}
.quick-cash-pill:hover {
    background: #db2777;
    color: #fff;
    border-color: #db2777;
    transform: translateY(-1px);
}
.kbd-shortcut {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.72rem;
    font-family: monospace;
    font-weight: bold;
    color: #475569;
}
</style>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Header Bar with Quick Stats & Shortcuts -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-cash-register text-primary"></i>
                        POS Cashier Register
                    </h2>
                    <p class="text-muted small mb-0">High-speed barcode, IMEI, and retail checkout terminal</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border p-2 px-3 small d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-circle text-primary"></i>
                        <span>Cashier: <strong><?= htmlspecialchars($current_user['fullname']) ?></strong></span>
                    </span>
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#recentSalesModal">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Today's Sales (<?= count($today_sales) ?>)
                    </button>
                    <span class="badge bg-light text-muted border small d-none d-lg-inline">
                        <kbd class="kbd-shortcut">/</kbd> Search &nbsp;|&nbsp; <kbd class="kbd-shortcut">F2</kbd> Pay &nbsp;|&nbsp; <kbd class="kbd-shortcut">Esc</kbd> Clear
                    </span>
                </div>
            </div>

            <!-- Checkout Success Notification Banner -->
            <?php if ($sale_success): ?>
                <div class="card card-custom border-success mb-4 p-3.5 bg-white shadow-sm animate-scale-in">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-success mb-1">Payment Received Successfully!</h5>
                                <div class="small text-muted">
                                    Invoice: <strong class="text-dark font-monospace"><?= $sale_success['invoice_no'] ?></strong> 
                                    &bull; Paid: <strong>₱<?= number_format($sale_success['amount_tendered'], 2) ?></strong>
                                    &bull; Change: <strong class="text-success fs-6">₱<?= number_format($sale_success['change_due'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($sale_success['invoice_no']) ?>&mode=thermal" target="_blank" class="btn btn-dark rounded-pill px-3.5 btn-tactile">
                                <i class="fa-solid fa-receipt me-1"></i> 80mm Thermal Slip
                            </a>
                            <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($sale_success['invoice_no']) ?>&mode=a4" target="_blank" class="btn btn-outline-dark rounded-pill px-3.5 btn-tactile">
                                <i class="fa-solid fa-file-invoice me-1"></i> A4 Tax Invoice
                            </a>
                        </div>
                    </div>
                </div>
            <?php elseif ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <i class="fa-solid <?= strpos($message, 'Error') !== false ? 'fa-circle-xmark' : 'fa-circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- ========================================================= -->
                <!-- LEFT COLUMN: SEARCH, CATEGORIES & SPACIOUS ITEM GRID      -->
                <!-- ========================================================= -->
                <div class="col-lg-7 col-xl-7">
                    <!-- Search Bar & Barcode Scanner Simulator -->
                    <div class="card card-custom p-3.5 mb-3 shadow-sm">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted px-3"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" id="posSearch" class="form-control form-control-lg border-start-0 ps-0 fs-6" placeholder="Type name, brand, SKU or barcode to search... (Press / to focus)" onkeyup="filterItems()">
                            <button class="btn btn-outline-secondary px-3" type="button" onclick="document.getElementById('posSearch').value=''; filterItems();" title="Clear Search">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Category Filter Pills (Clean, Non-Duplicated) -->
                        <div class="pos-cat-scroll">
                            <span class="pos-cat-pill active" onclick="filterCategory('all', this)">
                                <i class="fa-solid fa-border-all me-1"></i> All Items
                            </span>
                            <span class="pos-cat-pill" onclick="filterCategory('phones', this)">
                                <i class="fa-solid fa-mobile-screen me-1"></i> Pre-Owned Phones (<?= count($phone_units) ?>)
                            </span>
                            <?php foreach ($categories as $cat): ?>
                                <span class="pos-cat-pill" onclick="filterCategory('cat-<?= $cat['id'] ?>', this)">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Spacious Items Grid (2 cols on tablet, 3 cols on desktop) -->
                    <div class="row g-3" id="posItemsList" style="max-height: 640px; overflow-y: auto; padding-right: 6px;">
                        
                        <!-- 1. PHONE UNITS (IMEI TRACKED) -->
                        <?php foreach ($phone_units as $u): ?>
                            <div class="col-md-6 col-xl-4 pos-item" 
                                 data-category="phones" 
                                 data-name="<?= strtolower(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'] . ' ' . $u['imei_1'] . ' ' . $u['color'])) ?>" 
                                 data-barcode="<?= htmlspecialchars($u['imei_1']) ?>">
                                <div class="card card-custom pos-card-item border-dark" 
                                     onclick="posAddToCart('phone_unit', <?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'])) ?>', <?= $u['selling_price'] ?>, 1, '<?= htmlspecialchars($u['imei_1']) ?>')">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-dark text-white px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="fa-solid fa-mobile-screen me-1"></i><?= htmlspecialchars($u['brand']) ?>
                                        </span>
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.68rem;">
                                            <?= htmlspecialchars($u['unit_condition']) ?>
                                        </span>
                                    </div>
                                    <h6 class="pos-card-title mb-1" title="<?= htmlspecialchars($u['model_name']) ?>">
                                        <?= htmlspecialchars($u['model_name']) ?>
                                    </h6>
                                    <div class="text-muted small mb-1" style="font-size: 0.78rem;">
                                        <?= htmlspecialchars($u['storage_capacity']) ?> &bull; <?= htmlspecialchars($u['color']) ?>
                                    </div>
                                    <div class="text-muted small font-monospace mb-2" style="font-size: 0.72rem;">
                                        IMEI: <?= substr($u['imei_1'], 0, 8) ?>...
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                        <span class="pos-card-price">₱<?= number_format($u['selling_price'], 2) ?></span>
                                        <span class="pos-add-btn" title="Add phone to bill"><i class="fa-solid fa-plus"></i></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- 2. ACCESSORIES -->
                        <?php foreach ($products as $p): ?>
                            <div class="col-md-6 col-xl-4 pos-item" 
                                 data-category="cat-<?= $p['category_id'] ?>" 
                                 data-name="<?= strtolower(htmlspecialchars($p['name'] . ' ' . $p['brand'] . ' ' . $p['sku'] . ' ' . ($p['category_name'] ?? ''))) ?>" 
                                 data-barcode="<?= htmlspecialchars($p['sku']) ?>">
                                <div class="card card-custom pos-card-item" 
                                     onclick="posAddToCart('product', <?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['selling_price'] ?>, <?= $p['stock_quantity'] ?>, '<?= htmlspecialchars($p['sku']) ?>')">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-secondary border text-truncate px-2 py-1" style="font-size: 0.7rem; max-width: 60%;">
                                            <?= htmlspecialchars($p['brand'] ?: '8J') ?>
                                        </span>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 0.68rem;">
                                            Qty: <?= $p['stock_quantity'] ?>
                                        </span>
                                    </div>
                                    <h6 class="pos-card-title mb-1" title="<?= htmlspecialchars($p['name']) ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </h6>
                                    <div class="text-muted small font-monospace mb-2" style="font-size: 0.72rem;">
                                        SKU: <?= htmlspecialchars($p['sku']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                        <span class="pos-card-price">₱<?= number_format($p['selling_price'], 2) ?></span>
                                        <span class="pos-add-btn" title="Add item to bill"><i class="fa-solid fa-plus"></i></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- RIGHT COLUMN: CURRENT CART, BILL & TENDER (BREATHING ROOM) -->
                <!-- ========================================================= -->
                <div class="col-lg-5 col-xl-5">
                    <div class="card pos-cart-panel sticky-top" style="top: 80px;">
                        <!-- Cart Header -->
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-cart-shopping text-primary"></i>
                                Current Bill
                                <span class="badge bg-dark rounded-pill px-2.5 py-1" id="cartBadgeCount" style="font-size: 0.78rem;">0 items</span>
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="clearCart()" title="Empty cart (Esc)">
                                <i class="fa-solid fa-trash-can me-1"></i> Clear Cart
                            </button>
                        </div>

                        <!-- Cart Items Container -->
                        <div id="cartItemsContainer" class="pos-cart-list mb-3">
                            <div class="text-muted text-center py-5 small" id="emptyCartNotice">
                                <i class="fa-solid fa-basket-shopping fs-2 d-block mb-3 text-secondary opacity-50"></i>
                                Click any item on the left to add to bill.
                            </div>
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="border-top pt-3">
                            <div class="bill-summary-row">
                                <span class="text-muted">Subtotal:</span>
                                <strong id="billSubtotal">₱0.00</strong>
                            </div>

                            <!-- Discount Selector & Custom Discount -->
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-7">
                                    <label class="form-label small fw-semibold text-muted mb-1">Discount Preset</label>
                                    <select id="discountSelect" class="form-select form-select-sm" onchange="applyPresetDiscount()">
                                        <option value="0">No Discount (0%)</option>
                                        <option value="0.05">Promo Discount (5%)</option>
                                        <option value="0.10">Store Privilege (10%)</option>
                                        <option value="0.20">Senior / PWD (20%)</option>
                                        <option value="custom">Custom Flat ₱...</option>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label small fw-semibold text-muted mb-1">&nbsp;</label>
                                    <input type="number" step="0.01" id="customDiscountInput" class="form-control form-control-sm text-end" placeholder="₱ Flat Amount" style="display:none;" oninput="renderCartTotals()" value="0">
                                </div>
                            </div>

                            <div class="bill-summary-row text-danger" id="discountRow" style="display:none;">
                                <span>Discount Applied:</span>
                                <strong id="billDiscount">-₱0.00</strong>
                            </div>

                            <div class="bill-summary-row total-due">
                                <span>TOTAL DUE:</span>
                                <span id="billTotal">₱0.00</span>
                            </div>

                            <!-- Checkout Submission Form -->
                            <form method="POST" action="" id="checkoutForm" onsubmit="return validateCheckout()">
                                <input type="hidden" name="action" value="pos_checkout">
                                <input type="hidden" name="subtotal" id="hiddenSubtotal" value="0">
                                <input type="hidden" name="discount" id="hiddenDiscount" value="0">
                                <input type="hidden" name="total_amount" id="hiddenTotal" value="0">
                                <input type="hidden" name="cart_items" id="hiddenCartItems" value="[]">
                                <input type="hidden" name="customer_id" id="hiddenCustomerId" value="">

                                <!-- Customer Selector / Name Input -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold mb-1 d-flex justify-content-between">
                                        <span>Customer Name</span>
                                        <a href="javascript:void(0)" onclick="toggleCustomerSelect()" class="text-decoration-none small" style="font-size: 0.78rem;">Choose Registered</a>
                                    </label>
                                    <input type="text" name="customer_name" id="customerNameInput" class="form-control" value="Walk-in Customer" required>
                                    
                                    <!-- Hidden Select for Existing Customers -->
                                    <select id="customerSelectDropdown" class="form-select form-select-sm mt-2" style="display:none;" onchange="onSelectExistingCustomer(this)">
                                        <option value="">-- Choose Registered Customer --</option>
                                        <?php foreach ($customer_list as $cust): ?>
                                            <option value="<?= $cust['id'] ?>" data-name="<?= htmlspecialchars($cust['fullname']) ?>" data-phone="<?= htmlspecialchars($cust['contact_number']) ?>">
                                                <?= htmlspecialchars($cust['fullname']) ?> (<?= htmlspecialchars($cust['contact_number']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Payment Mode & Reference -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Payment Mode</label>
                                        <select name="payment_method" id="paymentMethodSelect" class="form-select" onchange="togglePaymentRef()">
                                            <option value="cash">Cash</option>
                                            <option value="gcash">GCash</option>
                                            <option value="card">Card / Maya</option>
                                            <option value="split">Split Payment</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Payment Ref #</label>
                                        <input type="text" name="payment_reference" id="paymentRefInput" class="form-control" placeholder="Optional notes">
                                    </div>
                                </div>

                                <!-- Amount Tendered & Quick Cash Chips -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-semibold mb-0">Amount Tendered (₱)</label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small fw-bold" onclick="tenderExactAmount()" style="font-size: 0.78rem;">
                                            Exact Amount
                                        </button>
                                    </div>
                                    <input type="number" step="0.01" name="amount_tendered" id="amountTendered" class="form-control form-control-lg fw-bold text-end font-monospace" placeholder="0.00" required oninput="calculateChange()">
                                    
                                    <!-- Fast Denomination Chips -->
                                    <div class="d-flex gap-2 flex-wrap mt-2" id="quickCashButtons">
                                        <span class="quick-cash-pill" onclick="addTender(100)">+₱100</span>
                                        <span class="quick-cash-pill" onclick="addTender(500)">+₱500</span>
                                        <span class="quick-cash-pill" onclick="addTender(1000)">+₱1,000</span>
                                        <span class="quick-cash-pill" onclick="setTenderExact()">Exact</span>
                                    </div>
                                </div>

                                <!-- Change Display -->
                                <div class="d-flex justify-content-between align-items-center p-3 mb-3.5 rounded bg-light border">
                                    <span class="text-muted small fw-semibold">Change Due:</span>
                                    <strong class="text-success fs-4 font-monospace" id="billChange">₱0.00</strong>
                                </div>

                                <!-- Action Buttons -->
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm btn-tactile fs-6" id="completeSaleBtn">
                                    <i class="fa-solid fa-circle-check me-2"></i> Complete Sale &amp; Print Receipt
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal: Today's Recent Sales & Fast Reprint -->
<div class="modal fade" id="recentSalesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Today's Sales &amp; Recent Invoices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th class="text-end">Total Amount</th>
                                <th>Time</th>
                                <th class="text-center">Reprint</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($today_sales)): ?>
                                <?php foreach ($today_sales as $ts): ?>
                                    <tr>
                                        <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($ts['invoice_no']) ?></td>
                                        <td><?= htmlspecialchars($ts['customer_name']) ?></td>
                                        <td><span class="badge bg-secondary text-uppercase" style="font-size: 0.68rem;"><?= $ts['payment_method'] ?></span></td>
                                        <td class="text-end fw-bold">₱<?= number_format($ts['total_amount'], 2) ?></td>
                                        <td class="text-muted small"><?= date('h:i A', strtotime($ts['created_at'])) ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($ts['invoice_no']) ?>&mode=thermal" target="_blank" class="btn btn-outline-dark" title="Print 80mm Slip">
                                                    <i class="fa-solid fa-receipt"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($ts['invoice_no']) ?>&mode=a4" target="_blank" class="btn btn-outline-secondary" title="Print A4 Tax Invoice">
                                                    <i class="fa-solid fa-file-invoice"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No sales recorded yet today.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?= BASE_URL ?>admin/reports.php" class="btn btn-outline-dark btn-sm rounded-pill">View All Sales Reports</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let posCart = [];

// Add Item to POS Cart
function posAddToCart(type, id, name, price, maxStock, identifier = '') {
    let existing = posCart.find(i => i.id === id && i.type === type);
    if (existing) {
        if (type === 'phone_unit') {
            alert('Phone units are unique IMEI serial devices and cannot exceed 1 unit.');
            return;
        }
        if (existing.quantity < maxStock) {
            existing.quantity++;
        } else {
            alert('Stock limit reached. Only ' + maxStock + ' units available in store.');
            return;
        }
    } else {
        posCart.push({
            type: type,
            id: id,
            name: name,
            price: parseFloat(price),
            quantity: 1,
            max: maxStock,
            identifier: identifier
        });
    }
    renderCart();
}

// Modify item quantity directly
function updatePosQty(index, delta) {
    if (!posCart[index]) return;
    let item = posCart[index];
    if (item.type === 'phone_unit') {
        if (delta < 0) removePosItem(index);
        return;
    }
    let newQty = item.quantity + delta;
    if (newQty <= 0) {
        removePosItem(index);
    } else if (newQty > item.max) {
        alert('Cannot exceed stock quantity of ' + item.max + ' units.');
    } else {
        item.quantity = newQty;
        renderCart();
    }
}

function removePosItem(index) {
    posCart.splice(index, 1);
    renderCart();
}

function clearCart() {
    if (posCart.length > 0 && !confirm('Are you sure you want to clear the current bill?')) return;
    posCart = [];
    renderCart();
    document.getElementById('amountTendered').value = '';
    calculateChange();
}

// Render Cart HTML
function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    const badge = document.getElementById('cartBadgeCount');
    
    let totalItems = posCart.reduce((sum, i) => sum + i.quantity, 0);
    badge.innerText = totalItems + (totalItems === 1 ? ' item' : ' items');

    if (posCart.length === 0) {
        container.innerHTML = `
            <div class="text-muted text-center py-5 small" id="emptyCartNotice">
                <i class="fa-solid fa-basket-shopping fs-2 d-block mb-3 text-secondary opacity-50"></i>
                Click any item on the left to add to bill.
            </div>`;
    } else {
        let html = '';
        posCart.forEach((item, idx) => {
            html += `
                <div class="pos-cart-row">
                    <div style="flex: 1; min-width: 0;">
                        <div class="fw-bold text-truncate" style="font-size: 0.88rem; color: #0f172a;">${item.name}</div>
                        <div class="text-muted small" style="font-size: 0.78rem;">
                            ₱${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})} each
                            ${item.type === 'phone_unit' ? '<span class="badge bg-dark text-white p-0 px-1 ms-1">IMEI</span>' : ''}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        ${item.type === 'product' ? `
                            <div class="pos-qty-pill-group">
                                <button type="button" class="pos-qty-btn" onclick="updatePosQty(${idx}, -1)">-</button>
                                <span class="small fw-bold px-2">${item.quantity}</span>
                                <button type="button" class="pos-qty-btn" onclick="updatePosQty(${idx}, 1)">+</button>
                            </div>
                        ` : `
                            <span class="badge bg-light text-dark border px-2 py-1">1 pc</span>
                        `}
                        <strong class="small" style="min-width: 65px; text-align: right; font-size: 0.88rem;">
                            ₱${(item.price * item.quantity).toLocaleString('en-US', {minimumFractionDigits: 2})}
                        </strong>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" onclick="removePosItem(${idx})" title="Remove item">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }
    renderCartTotals();
}

function applyPresetDiscount() {
    const sel = document.getElementById('discountSelect').value;
    const customInput = document.getElementById('customDiscountInput');
    if (sel === 'custom') {
        customInput.style.display = 'block';
        customInput.focus();
    } else {
        customInput.style.display = 'none';
        customInput.value = '0';
    }
    renderCartTotals();
}

function renderCartTotals() {
    let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    let sel = document.getElementById('discountSelect').value;
    let discAmount = 0;

    if (sel === 'custom') {
        discAmount = parseFloat(document.getElementById('customDiscountInput').value) || 0;
    } else {
        let discRate = parseFloat(sel) || 0;
        discAmount = subtotal * discRate;
    }
    discAmount = Math.min(subtotal, Math.max(0, discAmount));
    let total = Math.max(0, subtotal - discAmount);

    document.getElementById('billSubtotal').innerText = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    
    const discRow = document.getElementById('discountRow');
    if (discAmount > 0) {
        discRow.style.display = 'flex';
        document.getElementById('billDiscount').innerText = '-₱' + discAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
        discRow.style.display = 'none';
    }

    document.getElementById('billTotal').innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    document.getElementById('hiddenSubtotal').value = subtotal.toFixed(2);
    document.getElementById('hiddenDiscount').value = discAmount.toFixed(2);
    document.getElementById('hiddenTotal').value = total.toFixed(2);
    document.getElementById('hiddenCartItems').value = JSON.stringify(posCart);

    calculateChange();
}

function calculateChange() {
    let total = parseFloat(document.getElementById('hiddenTotal').value) || 0;
    let tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    let change = Math.max(0, tendered - total);
    document.getElementById('billChange').innerText = '₱' + change.toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Color alert on change box
    const changeBox = document.getElementById('billChange');
    if (tendered > 0 && tendered < total) {
        changeBox.className = 'text-danger fs-5 font-monospace';
        changeBox.innerText = 'Short: -₱' + (total - tendered).toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
        changeBox.className = 'text-success fs-4 font-monospace';
    }
}

// Quick tender shortcuts
function addTender(amt) {
    let cur = parseFloat(document.getElementById('amountTendered').value) || 0;
    document.getElementById('amountTendered').value = (cur + amt).toFixed(2);
    calculateChange();
}

function setTenderExact() {
    let total = parseFloat(document.getElementById('hiddenTotal').value) || 0;
    document.getElementById('amountTendered').value = total.toFixed(2);
    calculateChange();
}

function tenderExactAmount() {
    setTenderExact();
}

// Filter items by category tabs
function filterCategory(catKey, pillEl) {
    document.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
    pillEl.classList.add('active');

    let q = document.getElementById('posSearch').value.toLowerCase().trim();
    document.querySelectorAll('.pos-item').forEach(el => {
        let itemCat = el.dataset.category || '';
        let matchCat = (catKey === 'all' || itemCat === catKey);
        let matchSearch = q === '' || (el.dataset.name && el.dataset.name.includes(q)) || (el.dataset.barcode && el.dataset.barcode.toLowerCase().includes(q));
        el.style.display = (matchCat && matchSearch) ? 'block' : 'none';
    });
}

// Search filter
function filterItems() {
    let q = document.getElementById('posSearch').value.toLowerCase().trim();
    let activeCat = document.querySelector('.pos-cat-pill.active');
    let catKey = 'all';
    if (activeCat && activeCat.getAttribute('onclick')) {
        let m = activeCat.getAttribute('onclick').match(/filterCategory\('([^']+)'/);
        if (m) catKey = m[1];
    }

    document.querySelectorAll('.pos-item').forEach(el => {
        let itemCat = el.dataset.category || '';
        let matchCat = (catKey === 'all' || itemCat === catKey);
        let matchSearch = q === '' || (el.dataset.name && el.dataset.name.includes(q)) || (el.dataset.barcode && el.dataset.barcode.toLowerCase().includes(q));
        el.style.display = (matchCat && matchSearch) ? 'block' : 'none';
    });
}

// Existing customer toggle
function toggleCustomerSelect() {
    const sel = document.getElementById('customerSelectDropdown');
    sel.style.display = sel.style.display === 'none' ? 'block' : 'none';
}

function onSelectExistingCustomer(selectEl) {
    let opt = selectEl.options[selectEl.selectedIndex];
    if (opt.value) {
        document.getElementById('customerNameInput').value = opt.dataset.name;
        document.getElementById('hiddenCustomerId').value = opt.value;
    } else {
        document.getElementById('customerNameInput').value = 'Walk-in Customer';
        document.getElementById('hiddenCustomerId').value = '';
    }
}

// Payment Ref Toggle
function togglePaymentRef() {
    const mode = document.getElementById('paymentMethodSelect').value;
    const ref = document.getElementById('paymentRefInput');
    if (mode === 'cash') {
        ref.placeholder = 'Optional Notes';
    } else if (mode === 'gcash') {
        ref.placeholder = 'GCash Ref # (e.g. 100492819)';
    } else {
        ref.placeholder = 'Card / Approval Code';
    }
}

// Keyboard shortcuts (F2 to focus tender, / to focus search, Esc to clear)
document.addEventListener('keydown', function(e) {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT') {
        e.preventDefault();
        document.getElementById('posSearch').focus();
    } else if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('amountTendered').focus();
    } else if (e.key === 'Escape') {
        if (document.activeElement.tagName === 'INPUT') {
            document.activeElement.blur();
        }
    }
});

function validateCheckout() {
    if (posCart.length === 0) {
        alert('Cart is empty. Please select at least one accessory or phone unit.');
        return false;
    }
    let total = parseFloat(document.getElementById('hiddenTotal').value) || 0;
    let tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    if (tendered < total) {
        alert('Amount tendered (₱' + tendered.toFixed(2) + ') is less than Total Due (₱' + total.toFixed(2) + ').');
        document.getElementById('amountTendered').focus();
        return false;
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
