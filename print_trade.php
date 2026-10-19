<?php
/**
 * 8J Cellphone Accessories - Smartphone Trade-In / Swap Agreement Slip
 */
require_once __DIR__ . '/config/db.php';
require_login();

$trade_id = (int)($_GET['id'] ?? 0);
$trade_code = sanitize($_GET['code'] ?? '');

$stmt = $pdo->prepare("SELECT t.*, 
                              u_out.model_name as out_model, u_out.brand as out_brand, u_out.imei_1 as out_imei, u_out.storage_capacity as out_storage,
                              u_in.model_name as in_model, u_in.brand as in_brand, u_in.imei_1 as in_imei, u_in.storage_capacity as in_storage,
                              staff.fullname as staff_name
                       FROM trading_transactions t
                       LEFT JOIN phone_units u_out ON t.outgoing_unit_id = u_out.id
                       LEFT JOIN phone_units u_in ON t.incoming_unit_id = u_in.id
                       LEFT JOIN users staff ON t.handled_by_staff_id = staff.id
                       WHERE t.id = ? OR t.trade_code = ?");
$stmt->execute([$trade_id, $trade_code]);
$tr = $stmt->fetch();

if (!$tr) {
    die("<h3>Trade record not found.</h3>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade Agreement <?= htmlspecialchars($tr['trade_code']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 25px; color: #1e293b; }
        .contract-box { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: 800; color: #0d6efd; margin-bottom: 4px; }
        .sub { font-size: 12px; color: #64748b; }
        .doc-title { font-size: 15px; font-weight: bold; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 13px; margin-bottom: 20px; }
        .swap-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .unit-card { background: #f1f5f9; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px; }
        .unit-title { font-weight: bold; font-size: 14px; margin-bottom: 8px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        .balance-box { background: #f4f4f5; padding: 15px; border-radius: 6px; border: 1px solid #18181b; font-size: 14px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; font-size: 12px; }
        .sign-col { width: 220px; text-align: center; }
        .sign-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        .btn-print { background: #09090b; color: #fff; border: 1px solid #09090b; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-bottom: 20px; transition: background 0.2s ease; }
        .btn-print:hover { background: #27272a; }
        @media print { body { background: #fff; padding: 0; } .btn-print { display: none; } .contract-box { border: none; box-shadow: none; width: 100%; padding: 0; } }
    </style>
</head>
<body>
    <div style="max-width: 700px; margin: 0 auto;">
        <button class="btn-print" onclick="window.print()">Print Trade-In / Swap Agreement</button>

        <div class="contract-box">
            <div class="header text-center" style="text-align: center;">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 6px;" alt="8J Logo">
                <div class="title" style="font-weight: 800; font-size: 18px;">8J CELLPHONE ACCESSORIES</div>
                <div class="sub" style="font-size: 11px; color: #71717a;">Tanauan City, Batangas • Tel: 0917-123-4567</div>
                <div class="doc-title" style="font-weight: bold; margin-top: 6px; letter-spacing: 0.5px;">SMARTPHONE TRADE-IN & SWAP AGREEMENT</div>
            </div>

            <div class="meta-grid">
                <div>
                    <div><strong>Trade Code:</strong> <code><?= htmlspecialchars($tr['trade_code']) ?></code></div>
                    <div><strong>Customer Name:</strong> <?= htmlspecialchars($tr['customer_name']) ?></div>
                    <div><strong>Contact Number:</strong> <?= htmlspecialchars($tr['customer_contact']) ?></div>
                </div>
                <div style="text-align: right;">
                    <div><strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($tr['created_at'])) ?></div>
                    <div><strong>Attending Staff:</strong> <?= htmlspecialchars($tr['staff_name'] ?? 'Staff') ?></div>
                    <div><strong>Status:</strong> <span style="color: #09090b; font-weight: bold;">COMPLETED</span></div>
                </div>
            </div>

            <div class="swap-comparison">
                <!-- Unit Customer Gives -->
                <div class="unit-card">
                    <div class="unit-title" style="color: #09090b;">[INCOMING UNIT] From Customer</div>
                    <div><strong>Model:</strong> <?= htmlspecialchars($tr['in_brand'] . ' ' . $tr['in_model'] . ' ' . $tr['in_storage']) ?></div>
                    <div><strong>IMEI:</strong> <code><?= htmlspecialchars($tr['in_imei']) ?></code></div>
                    <div style="margin-top: 8px;"><strong>Appraised Trade Value:</strong> <span style="color: #09090b; font-weight: bold;">₱<?= number_format($tr['appraised_trade_value'], 2) ?></span></div>
                </div>

                <!-- Unit Customer Receives -->
                <div class="unit-card">
                    <div class="unit-title" style="color: #09090b;">[OUTGOING UNIT] To Customer</div>
                    <div><strong>Model:</strong> <?= htmlspecialchars($tr['out_brand'] . ' ' . $tr['out_model'] . ' ' . $tr['out_storage']) ?></div>
                    <div><strong>IMEI:</strong> <code><?= htmlspecialchars($tr['out_imei']) ?></code></div>
                    <div style="margin-top: 8px;"><strong>Selling Price:</strong> <span style="font-weight: bold;">₱<?= number_format($tr['target_unit_price'], 2) ?></span></div>
                </div>
            </div>

            <div class="balance-box">
                <div class="summary-row">
                    <span>Target Phone Price:</span>
                    <span>₱<?= number_format($tr['target_unit_price'], 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Trade-In Valuation Credit:</span>
                    <span>-₱<?= number_format($tr['appraised_trade_value'], 2) ?></span>
                </div>
                <div class="summary-row" style="border-top: 1px solid #93c5fd; padding-top: 5px; font-weight: bold; font-size: 15px;">
                    <span>NET TOP-UP CASH COLLECTED:</span>
                    <span style="color: #0d6efd;">₱<?= number_format($tr['cash_received_from_customer'], 2) ?></span>
                </div>
            </div>

            <div style="font-size: 11px; color: #64748b; line-height: 1.4; margin-top: 15px;">
                <strong>Trade Conditions:</strong> Both parties acknowledge the condition of the exchanged units. The customer confirms ownership of the traded-in phone and releases all claims. The store provides a 7-day replacement warranty on the outgoing unit for hidden defects (excluding accidental drops or water damage).
            </div>

            <div class="signatures">
                <div class="sign-col">
                    <div class="sign-line">Customer Signature</div>
                    <small><?= htmlspecialchars($tr['customer_name']) ?></small>
                </div>
                <div class="sign-col">
                    <div class="sign-line">Authorized Store Representative</div>
                    <small>8J Cellphone Accessories</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
