<?php
$page_title = "My Pick-Up Cart";
require_once __DIR__ . '/config/db.php';

$message = '';
$order_placed = null;

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_pickup_order') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $customer_contact = sanitize($_POST['customer_contact'] ?? '');
    $target_date = sanitize($_POST['pickup_target_date'] ?? date('Y-m-d'));
    $cart_data_json = $_POST['cart_data'] ?? '[]';
    $cart_items = json_decode($cart_data_json, true) ?: [];

    if (empty($cart_items)) {
        $message = "Error: Your cart is empty!";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Get or create customer user account
            $user_id = $current_user['id'] ?? null;
            if (!$user_id) {
                // Check if user with contact exists or create guest user
                $chk = $pdo->prepare("SELECT id FROM users WHERE contact_number = ? LIMIT 1");
                $chk->execute([$customer_contact]);
                $user_id = $chk->fetchColumn();

                if (!$user_id) {
                    $rand_pass = password_hash('customer123', PASSWORD_DEFAULT);
                    $rand_user = 'cust_' . time();
                    $ins_u = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role, contact_number) VALUES (?, ?, ?, ?, 'customer', ?)");
                    $ins_u->execute([$customer_name, $rand_user . '@8jclient.com', $rand_user, $rand_pass, $customer_contact]);
                    $user_id = $pdo->lastInsertId();
                }
            }

            // 2. Compute total
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += ((float)$item['price'] * (int)$item['quantity']);
            }

            // 3. Generate Codes
            $order_code = '8J-ORD-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $claim_code = '8J-' . rand(1000, 9999);

            $ins_ord = $pdo->prepare("INSERT INTO online_orders (order_code, customer_id, total_amount, pickup_target_date, pickup_status, claim_verification_code) VALUES (?, ?, ?, ?, 'pending_confirmation', ?)");
            $ins_ord->execute([$order_code, $user_id, $total_amount, $target_date, $claim_code]);
            $order_id = $pdo->lastInsertId();

            // 4. Insert items
            $ins_item = $pdo->prepare("INSERT INTO online_order_items (order_id, item_type, product_id, phone_unit_id, item_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $p_id = ($item['type'] === 'product') ? $item['id'] : null;
                $u_id = ($item['type'] === 'phone_unit') ? $item['id'] : null;
                $sub = (float)$item['price'] * (int)$item['quantity'];
                $ins_item->execute([$order_id, $item['type'], $p_id, $u_id, $item['name'], $item['price'], $item['quantity'], $sub]);

                // Reserve phone unit if applicable
                if ($u_id) {
                    $pdo->prepare("UPDATE phone_units SET status = 'reserved' WHERE id = ?")->execute([$u_id]);
                }
            }

            $pdo->commit();
            $order_placed = [
                'order_code' => $order_code,
                'claim_code' => $claim_code,
                'total_amount' => $total_amount,
                'pickup_target_date' => $target_date,
                'customer_name' => $customer_name
            ];
            $message = "Your online pick-up reservation has been confirmed!";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = "Order placement error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($order_placed): ?>
                <!-- Order Success / Pick-up Voucher Ticket -->
                <div class="card card-custom p-4 p-md-5 text-center border-dark bg-white shadow-sm mb-4 animate-scale-in">
                    <div class="bg-dark text-white d-inline-flex p-3 rounded-circle mb-3 fs-3 mx-auto">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Pick-Up Reservation Placed!</h3>
                    <p class="text-muted small">Present this voucher code to our cashier counter at 8J Cellphone Accessories, Tanauan City.</p>

                    <div class="p-3 bg-light rounded-3 border border-dark my-3 max-w-sm mx-auto shadow-sm" style="max-width: 400px;">
                        <span class="text-muted small text-uppercase fw-semibold d-block">Claim Verification Code</span>
                        <h1 class="display-5 fw-extrabold text-dark letter-spacing-1 font-monospace mb-0"><?= $order_placed['claim_code'] ?></h1>
                        <span class="text-muted small">Order Reference: <strong><?= $order_placed['order_code'] ?></strong></span>
                    </div>

                    <div class="row g-2 text-start small mb-4 justify-content-center" style="max-width: 450px; margin: 0 auto;">
                        <div class="col-6 text-muted">Customer Name:</div>
                        <div class="col-6 fw-bold"><?= htmlspecialchars($order_placed['customer_name']) ?></div>
                        <div class="col-6 text-muted">Pick-Up Date:</div>
                        <div class="col-6 fw-bold text-dark"><?= date('F d, Y', strtotime($order_placed['pickup_target_date'])) ?></div>
                        <div class="col-6 text-muted">Total Payable:</div>
                        <div class="col-6 fw-bold text-dark fs-6"><?= format_currency($order_placed['total_amount']) ?></div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-outline-dark rounded-pill px-4 btn-tactile" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print Voucher</button>
                        <a href="<?= BASE_URL ?>catalog.php" class="btn btn-primary rounded-pill px-4 btn-tactile">Continue Shopping</a>
                    </div>
                </div>

                <script>
                    localStorage.removeItem('8j_cart');
                    refreshCartBadge();
                </script>
            <?php else: ?>
                <!-- Cart Content -->
                <div class="card card-custom p-4 animate-fade-in-up">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h4 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <img src="<?= BASE_URL ?>assets/img/icons/cart.png" class="ui-icon" alt="Cart">
                            Review Your Pick-Up Cart
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="clearCart()">Clear Cart</button>
                    </div>

                    <div id="cartListContainer" class="mb-4">
                        <p class="text-muted text-center py-5">Your shopping cart is empty.</p>
                    </div>

                    <div id="checkoutSection" style="display: none;">
                        <div class="card bg-light p-3 border mb-4">
                            <div class="d-flex justify-content-between fs-5 fw-bold">
                                <span>Estimated Total:</span>
                                <span class="text-primary" id="cartTotalDisplay">₱0.00</span>
                            </div>
                            <small class="text-muted">Payment is completed upon item inspection at the store counter (Cash or GCash).</small>
                        </div>

                        <h5 class="fw-bold mb-3 border-bottom pb-2">Pick-Up Reservation Details</h5>
                        <form method="POST" action="" onsubmit="return submitCartOrder()">
                            <input type="hidden" name="action" value="place_pickup_order">
                            <input type="hidden" name="cart_data" id="hiddenCartData">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Your Full Name</label>
                                    <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($current_user['fullname'] ?? '') ?>" required placeholder="e.g. Maria Santos">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Mobile Contact Number</label>
                                    <input type="text" name="customer_contact" class="form-control" value="<?= htmlspecialchars($current_user['contact_number'] ?? '') ?>" required placeholder="09xxxxxxxxx">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Preferred Pick-Up Date</label>
                                <input type="date" name="pickup_target_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                                <div class="form-text small">Reservations are held for up to 48 hours from your selected date.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fa-solid fa-lock me-1"></i> Confirm & Generate Pick-Up Claim Code
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function renderCartView() {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    const container = document.getElementById('cartListContainer');
    const checkoutSec = document.getElementById('checkoutSection');

    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-5">Your shopping cart is currently empty. <a href="<?= BASE_URL ?>catalog.php">Explore our catalog</a>.</p>';
        if (checkoutSec) checkoutSec.style.display = 'none';
        return;
    }

    if (checkoutSec) checkoutSec.style.display = 'block';

    let html = '<div class="table-responsive"><table class="table align-middle"><thead class="table-light"><tr><th>Item</th><th class="text-center">Quantity</th><th class="text-end">Price</th><th class="text-end">Subtotal</th><th></th></tr></thead><tbody>';
    let total = 0;

    cart.forEach((item, idx) => {
        let sub = item.price * item.quantity;
        total += sub;
        html += `
            <tr>
                <td>
                    <div class="fw-bold">${item.name}</div>
                    <span class="badge bg-light text-secondary border" style="font-size:0.65rem;">${item.type === 'phone_unit' ? 'Phone Unit' : 'Accessory'}</span>
                </td>
                <td class="text-center">
                    ${item.type === 'phone_unit' ? '1' : `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${idx}, -1)">-</button>
                            <span class="btn btn-outline-secondary disabled fw-bold">${item.quantity}</span>
                            <button class="btn btn-outline-secondary" onclick="updateQty(${idx}, 1)">+</button>
                        </div>
                    `}
                </td>
                <td class="text-end text-muted">₱${item.price.toFixed(2)}</td>
                <td class="text-end fw-bold text-primary">₱${sub.toFixed(2)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-link text-danger" onclick="removeItem(${idx})"><i class="fa-solid fa-trash-can"></i></button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;

    const totalDisplay = document.getElementById('cartTotalDisplay');
    if (totalDisplay) {
        totalDisplay.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
    }
}

function updateQty(idx, change) {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    if (cart[idx]) {
        cart[idx].quantity += change;
        if (cart[idx].quantity <= 0) {
            cart.splice(idx, 1);
        }
        localStorage.setItem('8j_cart', JSON.stringify(cart));
        refreshCartBadge();
        renderCartView();
    }
}

function removeItem(idx) {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    cart.splice(idx, 1);
    localStorage.setItem('8j_cart', JSON.stringify(cart));
    refreshCartBadge();
    renderCartView();
}

function clearCart() {
    localStorage.removeItem('8j_cart');
    refreshCartBadge();
    renderCartView();
}

function submitCartOrder() {
    let cart = JSON.parse(localStorage.getItem('8j_cart') || '[]');
    if (cart.length === 0) {
        alert('Cart is empty!');
        return false;
    }
    document.getElementById('hiddenCartData').value = JSON.stringify(cart);
    return true;
}

document.addEventListener('DOMContentLoaded', renderCartView);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
