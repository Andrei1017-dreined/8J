<?php
$page_title = "POS & Billing Cashier";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';
$sale_success = null;

// Fetch products for POS item picker
$products = $pdo->query("SELECT id, name, brand, selling_price, stock_quantity, sku FROM products WHERE stock_quantity > 0 AND status = 'available'")->fetchAll();

// Fetch available phone units
$phone_units = $pdo->query("SELECT id, brand, model_name, storage_capacity, selling_price, imei_1, unit_condition FROM phone_units WHERE status = 'in_stock'")->fetchAll();

// Handle Checkout Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pos_checkout') {
    $customer_name = sanitize($_POST['customer_name'] ?? 'Walk-in Customer');
    $subtotal = (float)($_POST['subtotal'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $total_amount = (float)($_POST['total_amount'] ?? 0);
    $amount_tendered = (float)($_POST['amount_tendered'] ?? 0);
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash');
    $cart_items_json = $_POST['cart_items'] ?? '[]';
    $cart_items = json_decode($cart_items_json, true) ?: [];

    if (empty($cart_items)) {
        $message = "Error: Cart cannot be empty!";
    } elseif ($amount_tendered < $total_amount) {
        $message = "Error: Amount tendered is less than total amount due!";
    } else {
        try {
            $pdo->beginTransaction();

            $change_due = $amount_tendered - $total_amount;
            $invoice_no = '8J-INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            // 1. Insert into sales
            $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_name, subtotal, discount_amount, total_amount, amount_tendered, change_due, payment_method, cashier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$invoice_no, $customer_name, $subtotal, $discount, $total_amount, $amount_tendered, $change_due, $payment_method, $current_user['id']]);
            $sale_id = $pdo->lastInsertId();

            // 2. Insert items and update stock
            $item_stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, item_type, product_id, phone_unit_id, item_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($cart_items as $item) {
                if ($item['type'] === 'product') {
                    $item_stmt->execute([$sale_id, 'product', $item['id'], null, $item['name'], $item['price'], $item['quantity'], $item['price'] * $item['quantity']]);
                    // Deduct stock
                    $upd_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $upd_stock->execute([$item['quantity'], $item['id']]);
                } elseif ($item['type'] === 'phone_unit') {
                    $item_stmt->execute([$sale_id, 'phone_unit', null, $item['id'], $item['name'], $item['price'], 1, $item['price']]);
                    // Mark phone as sold
                    $upd_phone = $pdo->prepare("UPDATE phone_units SET status = 'sold' WHERE id = ?");
                    $upd_phone->execute([$item['id']]);
                }
            }

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

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Checkout error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-cash-register text-primary me-2"></i>POS & Billing Register</h2>
                    <p class="text-muted small mb-0">Cashier terminal for accessories retail sales and certified smartphone billing</p>
                </div>
                <div>
                    <span class="badge bg-light text-dark border p-2">Cashier: <strong><?= htmlspecialchars($current_user['fullname']) ?></strong></span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($sale_success): ?>
                <div class="card card-custom border-dark mb-4 p-4 bg-white shadow-sm animate-scale-in">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-circle-check me-2"></i>Payment Received!</h4>
                            <p class="mb-0 text-muted">Invoice: <strong class="text-dark"><?= $sale_success['invoice_no'] ?></strong> | Change Due: <strong class="text-dark"><?= format_currency($sale_success['change_due']) ?></strong></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>print_receipt.php?invoice=<?= urlencode($sale_success['invoice_no']) ?>" target="_blank" class="btn btn-dark rounded-pill px-4 btn-tactile">
                                <i class="fa-solid fa-print me-1"></i> Open & Print Thermal Receipt
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Item Selector Column -->
                <div class="col-lg-7">
                    <div class="card card-custom p-3 mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" id="posSearch" class="form-control border-start-0" placeholder="Search accessories or phone units..." onkeyup="filterItems()">
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div class="row g-2" id="posItemsList" style="max-height: 550px; overflow-y: auto;">
                        <!-- Accessories -->
                        <?php foreach ($products as $p): ?>
                            <div class="col-sm-6 col-md-4 pos-item" data-name="<?= strtolower(htmlspecialchars($p['name'] . ' ' . $p['brand'])) ?>">
                                <div class="card h-100 card-custom p-3 cursor-pointer stat-card" onclick="posAddToCart('product', <?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['selling_price'] ?>, <?= $p['stock_quantity'] ?>)">
                                    <div class="badge bg-light text-dark align-self-start mb-1" style="font-size: 0.68rem;"><?= htmlspecialchars($p['brand']) ?></div>
                                    <h6 class="fw-semibold text-truncate mb-1" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                        <span class="fw-bold text-dark"><?= format_currency($p['selling_price']) ?></span>
                                        <small class="text-muted">Qty: <?= $p['stock_quantity'] ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Phone Units -->
                        <?php foreach ($phone_units as $u): ?>
                            <div class="col-sm-6 col-md-4 pos-item" data-name="<?= strtolower(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['imei_1'])) ?>">
                                <div class="card h-100 card-custom p-3 cursor-pointer stat-card border-dark" onclick="posAddToCart('phone_unit', <?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['brand'] . ' ' . $u['model_name'] . ' ' . $u['storage_capacity'])) ?>', <?= $u['selling_price'] ?>, 1)">
                                    <div class="badge bg-dark text-white align-self-start mb-1" style="font-size: 0.68rem;"><?= htmlspecialchars($u['brand']) ?> Phone</div>
                                    <h6 class="fw-semibold text-truncate mb-1"><?= htmlspecialchars($u['model_name']) ?></h6>
                                    <small class="text-muted d-block mb-1">IMEI: <?= substr($u['imei_1'], 0, 8) ?>...</small>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                        <span class="fw-bold text-dark"><?= format_currency($u['selling_price']) ?></span>
                                        <span class="badge bg-light text-dark border">Unit</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cashier Bill & Cart Column -->
                <div class="col-lg-5">
                    <div class="card card-custom p-4 sticky-top" style="top: 80px;">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">Current Order</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">Clear</button>
                        </div>

                        <!-- Cart Items List -->
                        <div id="cartItemsContainer" style="max-height: 240px; overflow-y: auto;" class="mb-3">
                            <p class="text-muted text-center py-4 small" id="emptyCartNotice">Click any product on the left to add to bill.</p>
                        </div>

                        <!-- Calculations -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Subtotal:</span>
                                <strong id="billSubtotal">₱0.00</strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Customer Discount (Optional)</label>
                                <select id="discountSelect" class="form-select form-select-sm" onchange="renderCartTotals()">
                                    <option value="0">No Discount (0%)</option>
                                    <option value="0.05">Regular Promo Discount (5%)</option>
                                    <option value="0.20">Senior Citizen / PWD (20%)</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between fs-5 fw-extrabold mb-3">
                                <span>Total Due:</span>
                                <span class="text-primary" id="billTotal">₱0.00</span>
                            </div>

                            <!-- Checkout Form -->
                            <form method="POST" action="" id="checkoutForm" onsubmit="return validateCheckout()">
                                <input type="hidden" name="action" value="pos_checkout">
                                <input type="hidden" name="subtotal" id="hiddenSubtotal" value="0">
                                <input type="hidden" name="discount" id="hiddenDiscount" value="0">
                                <input type="hidden" name="total_amount" id="hiddenTotal" value="0">
                                <input type="hidden" name="cart_items" id="hiddenCartItems" value="[]">

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control form-control-sm" value="Walk-in Customer" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Payment Mode</label>
                                        <select name="payment_method" class="form-select form-select-sm">
                                            <option value="cash">Cash</option>
                                            <option value="gcash">GCash</option>
                                            <option value="card">Card / Maya</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Amount Tendered (₱)</label>
                                        <input type="number" step="0.01" name="amount_tendered" id="amountTendered" class="form-control form-control-sm" required oninput="calculateChange()">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between small text-muted mb-3 bg-light p-2 rounded">
                                    <span>Change Due:</span>
                                    <strong class="text-success fs-6" id="billChange">₱0.00</strong>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="fa-solid fa-check me-1"></i> Complete Sale & Issue Invoice
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
let posCart = [];

function posAddToCart(type, id, name, price, maxStock) {
    let existing = posCart.find(i => i.id === id && i.type === type);
    if (existing) {
        if (type === 'phone_unit') {
            alert('Phone units are individual IMEI items and cannot have quantity > 1.');
            return;
        }
        if (existing.quantity < maxStock) {
            existing.quantity++;
        } else {
            alert('Cannot exceed available stock quantity (' + maxStock + ')');
        }
    } else {
        posCart.push({ type: type, id: id, name: name, price: price, quantity: 1, max: maxStock });
    }
    renderCart();
}

function removePosItem(index) {
    posCart.splice(index, 1);
    renderCart();
}

function clearCart() {
    posCart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    if (posCart.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4 small" id="emptyCartNotice">Click any product on the left to add to bill.</p>';
    } else {
        let html = '<div class="list-group list-group-flush">';
        posCart.forEach((item, idx) => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                    <div style="max-width: 65%;">
                        <div class="fw-semibold text-truncate small">${item.name}</div>
                        <small class="text-muted">₱${item.price.toFixed(2)} x ${item.quantity}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <strong class="small">₱${(item.price * item.quantity).toFixed(2)}</strong>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removePosItem(${idx})"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }
    renderCartTotals();
}

function renderCartTotals() {
    let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    let discRate = parseFloat(document.getElementById('discountSelect').value) || 0;
    let discAmount = subtotal * discRate;
    let total = Math.max(0, subtotal - discAmount);

    document.getElementById('billSubtotal').innerText = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('billTotal').innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    document.getElementById('hiddenSubtotal').value = subtotal;
    document.getElementById('hiddenDiscount').value = discAmount;
    document.getElementById('hiddenTotal').value = total;
    document.getElementById('hiddenCartItems').value = JSON.stringify(posCart);

    calculateChange();
}

function calculateChange() {
    let total = parseFloat(document.getElementById('hiddenTotal').value) || 0;
    let tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    let change = Math.max(0, tendered - total);
    document.getElementById('billChange').innerText = '₱' + change.toLocaleString('en-US', {minimumFractionDigits: 2});
}

function validateCheckout() {
    if (posCart.length === 0) {
        alert('Please add at least one item to checkout.');
        return false;
    }
    let total = parseFloat(document.getElementById('hiddenTotal').value) || 0;
    let tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    if (tendered < total) {
        alert('Amount tendered must be at least equal to Total Due (₱' + total.toFixed(2) + ')');
        return false;
    }
    return true;
}

function filterItems() {
    let q = document.getElementById('posSearch').value.toLowerCase();
    document.querySelectorAll('.pos-item').forEach(el => {
        let text = el.dataset.name || '';
        el.style.display = text.includes(q) ? 'block' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
