<?php
/**
 * 8J Cellphone & Laptop Accessories IMS
 * Comprehensive Official Sales Receipt & Tax Invoice
 * Supports:
 *  - 80mm / 58mm POS Thermal Roll format
 *  - Standard Full Page (A4 / Letter) Invoice format
 *  - Live QR code verification
 *  - VAT Breakdown (12% VAT, Vatable Sales, VAT-Exempt)
 *  - Warranty Policy & Store Credentials
 */
require_once __DIR__ . '/config/db.php';

// Access Control: Allow authenticated staff/admin OR customers matching their own receipt
$sale_id = (int)($_GET['id'] ?? 0);
$invoice_no = sanitize($_GET['invoice'] ?? '');
$mode = sanitize($_GET['mode'] ?? 'thermal'); // 'thermal' or 'a4'

if (!$sale_id && empty($invoice_no)) {
    die("<div style='font-family:sans-serif;padding:30px;text-align:center;'><h3>Invalid receipt request.</h3><p>Please specify an Invoice Number or Sale ID.</p></div>");
}

$stmt = $pdo->prepare("SELECT s.*, u.fullname as cashier_name, c.email as customer_email, c.contact_number as customer_phone
                       FROM sales s 
                       LEFT JOIN users u ON s.cashier_id = u.id 
                       LEFT JOIN users c ON s.customer_id = c.id
                       WHERE s.id = ? OR s.invoice_no = ?");
$stmt->execute([$sale_id, $invoice_no]);
$sale = $stmt->fetch();

if (!$sale) {
    die("<div style='font-family:sans-serif;padding:30px;text-align:center;'><h3>Invoice record not found.</h3><p>The requested sales invoice does not exist or has been archived.</p></div>");
}

// Who may see it:
//  - staff, and the customer the sale belongs to: full receipt
//  - anyone holding the invoice number (the QR code printed on the receipt): receipt with contact details hidden
//  - looking receipts up by sequential ?id= needs staff or the owner
$is_owner = is_logged_in() && !empty($sale['customer_id']) && (int)$sale['customer_id'] === (int)$current_user['id'];
$full_view = is_staff() || $is_owner;
if (!$full_view) {
    if ($invoice_no === '' || strcasecmp($invoice_no, $sale['invoice_no']) !== 0) {
        if (!is_logged_in()) {
            header("Location: " . BASE_URL . "login.php");
        } else {
            header("Location: " . BASE_URL . "unauthorized.php");
        }
        exit;
    }
    $sale['customer_phone'] = '';
    $sale['customer_email'] = '';
}

// Fetch sale items with linked IMEI details if phone unit
$items_stmt = $pdo->prepare("
    SELECT si.*, 
           pu.imei_1, pu.imei_2, pu.color as phone_color, pu.unit_condition,
           p.sku as product_sku, p.category_id
    FROM sale_items si
    LEFT JOIN phone_units pu ON si.phone_unit_id = pu.id
    LEFT JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
    ORDER BY si.id ASC
");
$items_stmt->execute([$sale['id']]);
$items = $items_stmt->fetchAll();

// Store Details
$store = [
    'name' => '8J CELLPHONE & ACCESSORIES',
    'tagline' => 'Retail • Smartphone Swaps • Certified Pre-Owned • Repairs',
    'location' => '2nd Floor, Victory Mall Tanauan, Stall #S1, Tanauan City, Batangas',
    'tin' => 'VAT Reg. ' . STORE_TIN,
    'hotline' => '0909-9833-981 / 0917-123-4567',
    'email' => 'sales@8jaccessories.ph',
    'website' => BASE_URL // already includes the protocol and host
];

// Calculation of VAT (Philippines 12% VAT inclusive calculation)
// Total Amount = Vatable Sales * 1.12
$total_amount = (float)$sale['total_amount'];
$vatable_sales = round($total_amount / (1 + VAT_RATE), 2);
$vat_amount = round($total_amount - $vatable_sales, 2);

// Verification URL for QR code
$verify_url = $store['website'] . 'print_receipt.php?invoice=' . urlencode($sale['invoice_no']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= htmlspecialchars($sale['invoice_no']) ?> - 8J Accessories</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #9c27b0;
            --primary-dark: #6a1b9a;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Action Toolbar */
        .no-print-toolbar {
            width: 100%;
            max-width: 800px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            padding: 12px 18px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-act {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-act:hover {
            background: #334155;
            color: #ffffff;
        }
        .btn-act.btn-outline {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }
        .btn-act.btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        .btn-act.active-mode {
            background: var(--primary);
            color: #ffffff;
            border: 1px solid var(--primary-dark);
        }

        /* MODE 1: THERMAL RECEIPT CONTAINER */
        .thermal-wrapper {
            background: #ffffff;
            width: 330px;
            padding: 24px 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
        }
        .thermal-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 12px;
            margin-bottom: 10px;
        }
        .thermal-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .thermal-store-name {
            font-size: 14px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 3px;
        }
        .thermal-meta {
            font-size: 11px;
            color: #222;
            line-height: 1.3;
        }
        .thermal-info-block {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #000;
        }
        .thermal-info-row {
            display: flex;
            justify-content: space-between;
        }
        .thermal-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin: 8px 0;
        }
        .thermal-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .thermal-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .thermal-total-section {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin: 8px 0;
            font-size: 12px;
        }
        .thermal-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .thermal-row.bold {
            font-weight: bold;
            font-size: 14px;
        }
        .thermal-tax-breakdown {
            font-size: 10px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #000;
            margin-bottom: 8px;
        }
        .thermal-footer {
            text-align: center;
            font-size: 10px;
            line-height: 1.4;
            margin-top: 12px;
        }
        .thermal-qr {
            margin: 12px auto;
            display: flex;
            justify-content: center;
        }

        /* MODE 2: FULL PAGE INVOICE CONTAINER (A4 / Letter) */
        .a4-wrapper {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            padding: 45px 50px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
        }
        .a4-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .a4-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .a4-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
        }
        .a4-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }
        .a4-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }
        .a4-badge-invoice {
            text-align: right;
        }
        .a4-invoice-pill {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .a4-grid-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .a4-card-meta {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 12px;
        }
        .a4-meta-label {
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .a4-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .a4-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
        }
        .a4-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .a4-summary-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .a4-totals-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px 20px;
            font-size: 13px;
        }
        .a4-totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .a4-totals-row.grand-total {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 8px 0;
            margin: 10px 0;
        }
        .a4-legal-box {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 14px;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
            background: #fff;
        }
        .a4-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
        }
        .a4-sign-line {
            border-top: 1px solid #0f172a;
            padding-top: 6px;
            margin-top: 45px;
            font-weight: 600;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .thermal-wrapper {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 80mm;
                margin: 0 auto;
            }
            .a4-wrapper {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- On-Screen Navigation & Print Switcher Bar -->
    <div class="no-print-toolbar">
        <div class="toolbar-group">
            <a href="<?= BASE_URL ?>admin/pos.php" class="btn-act btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Back to POS
            </a>
            <a href="<?= BASE_URL ?>admin/reports.php" class="btn-act btn-outline">
                <i class="fa-solid fa-chart-line"></i> Sales Reports
            </a>
        </div>

        <div class="toolbar-group">
            <span style="font-size: 12px; font-weight: 600; color: #64748b;">Receipt Format:</span>
            <a href="?<?= http_build_query(array_merge($_GET, ['mode' => 'thermal'])) ?>" class="btn-act btn-outline <?= $mode === 'thermal' ? 'active-mode' : '' ?>">
                <i class="fa-solid fa-receipt"></i> 80mm POS Thermal Roll
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['mode' => 'a4'])) ?>" class="btn-act btn-outline <?= $mode === 'a4' ? 'active-mode' : '' ?>">
                <i class="fa-solid fa-file-invoice"></i> A4 / Letter Tax Invoice
            </a>
        </div>

        <div class="toolbar-group">
            <button type="button" class="btn-act" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Receipt (Ctrl+P)
            </button>
        </div>
    </div>

    <?php if ($mode === 'a4'): ?>
        <!-- ========================================================================= -->
        <!-- FORMAT 2: FULL A4 / LETTER TAX INVOICE & PROOF OF PURCHASE                 -->
        <!-- ========================================================================= -->
        <div class="a4-wrapper">
            <div class="a4-header">
                <div class="a4-brand">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" class="a4-logo" alt="8J Logo">
                    <div>
                        <h1 class="a4-title"><?= $store['name'] ?></h1>
                        <p class="a4-sub"><?= $store['tagline'] ?></p>
                        <p class="a4-sub"><strong>Location:</strong> <?= $store['location'] ?></p>
                        <p class="a4-sub"><strong>TIN:</strong> <?= $store['tin'] ?> | <strong>Contact:</strong> <?= $store['hotline'] ?></p>
                    </div>
                </div>
                <div class="a4-badge-invoice">
                    <div class="a4-invoice-pill">OFFICIAL SALES INVOICE</div>
                    <div style="font-family: monospace; font-size: 15px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($sale['invoice_no']) ?></div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 3px;">Date: <?= date('F d, Y h:i A', strtotime($sale['created_at'])) ?></div>
                </div>
            </div>

            <div class="a4-grid-meta">
                <div class="a4-card-meta">
                    <div class="a4-meta-label">Billed To (Customer Information)</div>
                    <div style="font-size: 14px; font-weight: 700; margin-bottom: 2px;"><?= htmlspecialchars($sale['customer_name']) ?></div>
                    <?php if (!empty($sale['customer_phone'])): ?>
                        <div style="color: #64748b;"><i class="fa-solid fa-phone me-1" style="font-size: 10px;"></i><?= htmlspecialchars($sale['customer_phone']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($sale['customer_email'])): ?>
                        <div style="color: #64748b;"><i class="fa-solid fa-envelope me-1" style="font-size: 10px;"></i><?= htmlspecialchars($sale['customer_email']) ?></div>
                    <?php endif; ?>
                    <div style="color: #64748b; margin-top: 4px;">Walk-in In-Store / Online Reservation Checkout</div>
                </div>

                <div class="a4-card-meta">
                    <div class="a4-meta-label">Transaction & Cashier Details</div>
                    <div><strong>Cashier / Attendant:</strong> <?= htmlspecialchars($sale['cashier_name'] ?? 'Authorized Staff') ?></div>
                    <div><strong>Payment Method:</strong> <?= strtoupper($sale['payment_method']) ?> <?= !empty($sale['payment_reference']) ? '(' . htmlspecialchars($sale['payment_reference']) . ')' : '' ?></div>
                    <div><strong>Payment Status:</strong> <span style="color: #16a34a; font-weight: 700;">PAID IN FULL</span></div>
                    <div><strong>Terminal / Location:</strong> POS Register #01 (Victory Mall Tanauan)</div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="a4-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 48%;">Item Description / Serial / IMEI</th>
                        <th style="width: 14%; text-align: center;">Qty</th>
                        <th style="width: 15%; text-align: right;">Unit Price</th>
                        <th style="width: 15%; text-align: right;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $line_no = 1; foreach ($items as $item): ?>
                        <tr>
                            <td style="color: #64748b;"><?= $line_no++ ?></td>
                            <td>
                                <strong style="color: #0f172a; font-size: 13.5px;"><?= htmlspecialchars($item['item_name']) ?></strong>
                                <?php if ($item['item_type'] === 'phone_unit'): ?>
                                    <div style="font-size: 11px; color: #475569; margin-top: 3px;">
                                        <span class="badge" style="background: #0f172a; color:#fff; padding: 2px 6px; border-radius: 4px;">Certified Pre-Owned</span>
                                        <?php if (!empty($item['imei_1'])): ?>
                                            &bull; <strong>IMEI:</strong> <span style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($item['imei_1']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['phone_color'])): ?>
                                            &bull; Color: <?= htmlspecialchars($item['phone_color']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($item['unit_condition'])): ?>
                                            &bull; Grade: <?= strtoupper(htmlspecialchars($item['unit_condition'])) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif (!empty($item['product_sku'])): ?>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        SKU: <?= htmlspecialchars($item['product_sku']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 600;"><?= $item['quantity'] ?></td>
                            <td style="text-align: right;"><?= format_currency($item['unit_price']) ?></td>
                            <td style="text-align: right; font-weight: 700;"><?= format_currency($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="a4-summary-grid">
                <!-- QR Code & Warranty Info -->
                <div class="a4-legal-box">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div id="qrcodeA4" style="flex-shrink: 0; padding: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px;"></div>
                        <div>
                            <strong style="color: #0f172a; font-size: 12px;">Scan to Verify Authenticity</strong>
                            <p style="margin: 3px 0 0 0; font-size: 11px; color: #64748b;">
                                Point camera to scan and confirm official registration in 8J Store Cloud Database.
                            </p>
                        </div>
                    </div>
                    <div style="margin-top: 14px; border-top: 1px dashed #cbd5e1; padding-top: 10px;">
                        <strong style="color: #0f172a;">Store Warranty & Policy:</strong>
                        <ul style="margin: 4px 0 0 0; padding-left: 18px; font-size: 10.5px;">
                            <li><strong>Accessories:</strong> 3-day replacement warranty for factory defects (with complete packaging).</li>
                            <li><strong>Phone Units:</strong> 7-day replacement warranty + 1-year service warranty against technical defects (void if jailbroken/water damaged/dropped).</li>
                            <li>Official Sales Receipt must be presented for all warranty and return inquiries.</li>
                        </ul>
                    </div>
                </div>

                <!-- Financial Totals -->
                <div class="a4-totals-box">
                    <div class="a4-totals-row">
                        <span style="color: #64748b;">Subtotal:</span>
                        <strong><?= format_currency($sale['subtotal']) ?></strong>
                    </div>
                    <?php if ($sale['discount_amount'] > 0): ?>
                        <div class="a4-totals-row" style="color: #dc2626;">
                            <span>Discount / Promo Applied:</span>
                            <strong>-<?= format_currency($sale['discount_amount']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="a4-totals-row grand-total">
                        <span>TOTAL AMOUNT DUE:</span>
                        <span><?= format_currency($sale['total_amount']) ?></span>
                    </div>
                    <div class="a4-totals-row">
                        <span style="color: #64748b;">Amount Tendered (<?= strtoupper($sale['payment_method']) ?>):</span>
                        <strong><?= format_currency($sale['amount_tendered']) ?></strong>
                    </div>
                    <div class="a4-totals-row">
                        <span style="color: #64748b;">Change Due:</span>
                        <strong style="color: #16a34a;"><?= format_currency($sale['change_due']) ?></strong>
                    </div>

                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 11px; color: #64748b;">
                        <div class="a4-totals-row">
                            <span>VATable Sales (net of VAT):</span>
                            <span><?= format_currency($vatable_sales) ?></span>
                        </div>
                        <div class="a4-totals-row">
                            <span>Value Added Tax (12% VAT):</span>
                            <span><?= format_currency($vat_amount) ?></span>
                        </div>
                        <div class="a4-totals-row">
                            <span>VAT-Exempt Sales:</span>
                            <span>₱0.00</span>
                        </div>
                        <div class="a4-totals-row">
                            <span>Zero-Rated Sales:</span>
                            <span>₱0.00</span>
                        </div>
                        <div class="a4-totals-row" style="font-weight: 700; color: #334155;">
                            <span>Total (VAT inclusive):</span>
                            <span><?= format_currency($total_amount) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Lines -->
            <div class="a4-signatures">
                <div>
                    <div class="a4-sign-line"><?= htmlspecialchars($sale['cashier_name'] ?? 'Authorized Cashier') ?></div>
                    <span style="color: #64748b; font-size: 11px;">Store Cashier / Attendant</span>
                </div>
                <div>
                    <div class="a4-sign-line"><?= htmlspecialchars($sale['customer_name']) ?></div>
                    <span style="color: #64748b; font-size: 11px;">Customer Signature / Conforme</span>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ========================================================================= -->
        <!-- FORMAT 1: 80MM / 58MM STANDARD POS THERMAL RECEIPT ROLL                   -->
        <!-- ========================================================================= -->
        <div class="thermal-wrapper">
            <div class="thermal-header">
                <img src="<?= BASE_URL ?>assets/img/logo.png" class="thermal-logo" alt="8J Logo">
                <div class="thermal-store-name"><?= $store['name'] ?></div>
                <div class="thermal-meta"><?= $store['location'] ?></div>
                <div class="thermal-meta">TIN: <?= $store['tin'] ?></div>
                <div class="thermal-meta">Hotline: <?= $store['hotline'] ?></div>
                <div style="font-weight: bold; margin-top: 6px; font-size: 12px; letter-spacing: 0.5px;">OFFICIAL SALES INVOICE</div>
            </div>

            <div class="thermal-info-block">
                <div class="thermal-info-row">
                    <span>INVOICE #:</span>
                    <strong><?= htmlspecialchars($sale['invoice_no']) ?></strong>
                </div>
                <div class="thermal-info-row">
                    <span>DATE & TIME:</span>
                    <span><?= date('m/d/Y h:i A', strtotime($sale['created_at'])) ?></span>
                </div>
                <div class="thermal-info-row">
                    <span>CASHIER:</span>
                    <span><?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></span>
                </div>
                <div class="thermal-info-row">
                    <span>CUSTOMER:</span>
                    <span><?= htmlspecialchars($sale['customer_name']) ?></span>
                </div>
            </div>

            <!-- Itemized List -->
            <table class="thermal-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">ITEM</th>
                        <th style="width: 15%; text-align: center;">QTY</th>
                        <th style="width: 35%; text-align: right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div><?= htmlspecialchars($item['item_name']) ?></div>
                                <?php if ($item['item_type'] === 'phone_unit'): ?>
                                    <div style="font-size: 9px; color: #333;">
                                        [PHONE UNIT] <?= !empty($item['imei_1']) ? 'IMEI: ' . htmlspecialchars($item['imei_1']) : '' ?>
                                    </div>
                                <?php endif; ?>
                                <div style="font-size: 10px; color: #555;">@ ₱<?= number_format($item['unit_price'], 2) ?></div>
                            </td>
                            <td style="text-align: center; vertical-align: top;"><?= $item['quantity'] ?></td>
                            <td style="text-align: right; vertical-align: top; font-weight: bold;">
                                ₱<?= number_format($item['subtotal'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Financial Totals -->
            <div class="thermal-total-section">
                <div class="thermal-row">
                    <span>Subtotal:</span>
                    <span>₱<?= number_format($sale['subtotal'], 2) ?></span>
                </div>
                <?php if ($sale['discount_amount'] > 0): ?>
                    <div class="thermal-row">
                        <span>Discount:</span>
                        <span>-₱<?= number_format($sale['discount_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="thermal-row bold" style="margin-top: 4px; font-size: 13px;">
                    <span>TOTAL AMOUNT:</span>
                    <span>₱<?= number_format($sale['total_amount'], 2) ?></span>
                </div>
                <div class="thermal-row" style="margin-top: 6px;">
                    <span>Payment (<?= strtoupper($sale['payment_method']) ?>):</span>
                    <span>₱<?= number_format($sale['amount_tendered'], 2) ?></span>
                </div>
                <div class="thermal-row">
                    <span>Change Due:</span>
                    <span>₱<?= number_format($sale['change_due'], 2) ?></span>
                </div>
            </div>

            <!-- Tax Summary -->
            <div class="thermal-tax-breakdown">
                <div class="thermal-row">
                    <span>VATable Sales:</span>
                    <span>₱<?= number_format($vatable_sales, 2) ?></span>
                </div>
                <div class="thermal-row">
                    <span>12% VAT:</span>
                    <span>₱<?= number_format($vat_amount, 2) ?></span>
                </div>
                <div class="thermal-row">
                    <span>VAT-Exempt Sales:</span>
                    <span>₱0.00</span>
                </div>
                <div class="thermal-row">
                    <span>Zero-Rated Sales:</span>
                    <span>₱0.00</span>
                </div>
            </div>

            <!-- Verification QR Code -->
            <div class="thermal-qr">
                <div id="qrcodeThermal"></div>
            </div>

            <div class="thermal-footer">
                <div style="font-weight: bold;">THANK YOU FOR YOUR PURCHASE!</div>
                <div style="margin-top: 4px;">Accessories: 3-Day Replacement for Factory Defects</div>
                <div>Phone Units: 7-Day Replacement &bull; 1-Year Warranty</div>
                <div style="margin-top: 4px; font-style: italic;">Please keep this receipt for warranty claims.</div>
                <div style="margin-top: 6px; font-size: 9px; color: #444;">System Generated &bull; 8J IMS Victory Mall</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- QR Code Script -->
    <script src="<?= BASE_URL ?>assets/js/qrcode.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const verifyData = "<?= $verify_url ?>";
        
        <?php if ($mode === 'a4'): ?>
        const containerA4 = document.getElementById("qrcodeA4");
        if (containerA4 && typeof QRCode !== 'undefined') {
            new QRCode(containerA4, {
                text: verifyData,
                width: 90,
                height: 90,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        }
        <?php else: ?>
        const containerThermal = document.getElementById("qrcodeThermal");
        if (containerThermal && typeof QRCode !== 'undefined') {
            new QRCode(containerThermal, {
                text: verifyData,
                width: 100,
                height: 100,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        }
        <?php endif; ?>
    });
    </script>
</body>
</html>
