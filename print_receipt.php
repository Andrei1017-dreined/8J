<?php
/**
 * 8J Cellphone Accessories - Printable POS Sales Invoice
 */
require_once __DIR__ . '/config/db.php';
require_login();

$sale_id = (int)($_GET['id'] ?? 0);
$invoice_no = sanitize($_GET['invoice'] ?? '');

$stmt = $pdo->prepare("SELECT s.*, u.fullname as cashier_name 
                       FROM sales s 
                       LEFT JOIN users u ON s.cashier_id = u.id 
                       WHERE s.id = ? OR s.invoice_no = ?");
$stmt->execute([$sale_id, $invoice_no]);
$sale = $stmt->fetch();

if (!$sale) {
    die("<h3>Invoice record not found.</h3>");
}

// Fetch sale items
$items_stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
$items_stmt->execute([$sale['id']]);
$items = $items_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?= htmlspecialchars($sale['invoice_no']) ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .receipt-card {
            background: #fff;
            width: 320px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid #cbd5e1;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #475569; padding-top: 8px; margin-top: 8px; }
        .border-bottom { border-bottom: 1px dashed #475569; padding-bottom: 8px; margin-bottom: 8px; }
        .receipt-title { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .receipt-sub { font-size: 11px; color: #475569; }
        .table-items { width: 100%; font-size: 12px; border-collapse: collapse; margin: 10px 0; }
        .table-items th { text-align: left; border-bottom: 1px solid #000; padding: 4px 0; }
        .table-items td { padding: 4px 0; vertical-align: top; }
        .summary-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
        .btn-print {
            display: block;
            width: 100%;
            background: #09090b;
            color: #fff;
            border: 1px solid #09090b;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 15px;
            font-family: sans-serif;
            font-weight: bold;
            transition: background 0.2s ease;
        }
        .btn-print:hover {
            background: #27272a;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { box-shadow: none; border: none; width: 100%; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div>
        <button class="btn-print" onclick="window.print()">Print Official Receipt</button>

        <div class="receipt-card">
            <div class="text-center border-bottom">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 6px;" alt="8J Logo">
                <div class="receipt-title">8J CELLPHONE ACCESSORIES</div>
                <div class="receipt-sub">Tanauan City, Batangas</div>
                <div class="receipt-sub">Tel: 0917-123-4567</div>
                <div class="receipt-sub">OFFICIAL SALES INVOICE</div>
            </div>

            <div style="font-size: 11px; margin-bottom: 10px;">
                <div><strong>Invoice #:</strong> <?= htmlspecialchars($sale['invoice_no']) ?></div>
                <div><strong>Date:</strong> <?= date('m/d/Y h:i A', strtotime($sale['created_at'])) ?></div>
                <div><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></div>
                <div><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name']) ?></div>
            </div>

            <table class="table-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                                <?php if ($item['item_type'] === 'phone_unit'): ?>
                                    <div style="font-size: 10px; color: #555;">[Phone Unit]</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                            <td class="text-end"><?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="border-top">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₱<?= number_format($sale['subtotal'], 2) ?></span>
                </div>
                <?php if ($sale['discount_amount'] > 0): ?>
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span>-₱<?= number_format($sale['discount_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row fw-bold" style="font-size: 14px; margin-top: 4px;">
                    <span>TOTAL DUE:</span>
                    <span>₱<?= number_format($sale['total_amount'], 2) ?></span>
                </div>
                <div class="summary-row" style="margin-top: 6px;">
                    <span>Payment (<?= strtoupper($sale['payment_method']) ?>):</span>
                    <span>₱<?= number_format($sale['amount_tendered'], 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Change:</span>
                    <span>₱<?= number_format($sale['change_due'], 2) ?></span>
                </div>
            </div>

            <div class="text-center border-top" style="margin-top: 15px; font-size: 11px;">
                <div>Thank you for your purchase!</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                    Accessories: 7-day replacement for factory defects.<br>
                    Keep this receipt for warranty claims.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
