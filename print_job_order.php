<?php
/**
 * 8J Cellphone Accessories - Printable Repair Job Order Claim Stub
 */
require_once __DIR__ . '/config/db.php';
require_login();

$job_id = (int)($_GET['id'] ?? 0);
$ticket_no = sanitize($_GET['ticket'] ?? '');

$stmt = $pdo->prepare("SELECT r.*, t.fullname as tech_name 
                       FROM repair_job_orders r 
                       LEFT JOIN users t ON r.technician_id = t.id 
                       WHERE r.id = ? OR r.job_order_no = ?");
$stmt->execute([$job_id, $ticket_no]);
$job = $stmt->fetch();

if (!$job) {
    die("<h3>Repair Job Order not found.</h3>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Order <?= htmlspecialchars($job['job_order_no']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .container {
            max-width: 750px;
            margin: 0 auto;
        }
        .ticket {
            background: #fff;
            padding: 24px;
            border: 2px dashed #94a3b8;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #09090b;
        }
        .brand-sub {
            font-size: 11px;
            color: #71717a;
        }
        .ticket-number {
            text-align: right;
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            color: #09090b;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .info-box {
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .info-label {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-weight: 600;
        }
        .terms {
            font-size: 10px;
            color: #64748b;
            line-height: 1.4;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 15px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            font-size: 12px;
        }
        .sign-line {
            width: 200px;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
        }
        .btn-print {
            background: #09090b;
            color: #fff;
            border: 1px solid #09090b;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background 0.2s ease;
        }
        .btn-print:hover {
            background: #27272a;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .btn-print { display: none; }
            .ticket { box-shadow: none; border-color: #000; }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn-print" onclick="window.print()">Print Job Order Claim Stub (2 Copies)</button>

        <!-- COPY 1: CUSTOMER CLAIM STUB -->
        <div class="ticket">
            <div class="header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 44px; height: 44px; object-fit: contain;" alt="8J Logo">
                    <div>
                        <div class="brand-title">8J CELLPHONE ACCESSORIES & REPAIR</div>
                        <div class="brand-sub">Tanauan City, Batangas • Contact: 0917-123-4567</div>
                        <div style="font-size: 11px; font-weight: bold; color: #09090b; margin-top: 2px;">CUSTOMER CLAIM STUB</div>
                    </div>
                </div>
                <div>
                    <div class="ticket-number"><?= htmlspecialchars($job['job_order_no']) ?></div>
                    <div style="font-size: 11px; color: #64748b;">Date: <?= date('m/d/Y', strtotime($job['created_at'])) ?></div>
                </div>
            </div>

            <div class="grid-2">
                <div class="info-box">
                    <div class="info-label">Customer Information</div>
                    <div class="info-value"><?= htmlspecialchars($job['customer_name']) ?></div>
                    <div>Contact: <?= htmlspecialchars($job['customer_contact']) ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Device & Hardware Info</div>
                    <div class="info-value"><?= htmlspecialchars($job['device_brand'] . ' ' . $job['device_model']) ?></div>
                    <div>IMEI/SN: <?= htmlspecialchars($job['device_imei_or_serial'] ?: 'N/A') ?></div>
                </div>
            </div>

            <div class="info-box" style="margin-bottom: 15px;">
                <div class="info-label">Reported Defect & Issues</div>
                <div style="font-size: 13px;"><?= htmlspecialchars($job['reported_issues']) ?></div>
            </div>

            <div class="grid-2">
                <div class="info-box">
                    <div class="info-label">Estimated Bill / Parts & Labor</div>
                    <div class="info-value" style="font-size: 16px; color: #0d6efd;">₱<?= number_format($job['estimated_cost'], 2) ?></div>
                    <div style="font-size: 12px; color: green;">Deposit Paid: ₱<?= number_format($job['deposit_paid'], 2) ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Assigned Technician & Status</div>
                    <div class="info-value"><?= htmlspecialchars($job['tech_name'] ?? 'In Queue') ?></div>
                    <div style="font-size: 12px;">Status: <strong><?= strtoupper(str_replace('_', ' ', $job['status'])) ?></strong></div>
                </div>
            </div>

            <div class="terms">
                <strong>Terms & Conditions:</strong><br>
                1. Please present this claim stub when picking up your repaired unit.<br>
                2. Repaired hardware components come with a standard <strong>30-day service warranty</strong> (void if opened by third party, water-damaged, or physically cracked).<br>
                3. Units left unclaimed after <strong>60 days</strong> will be forfeited to cover parts and storage expenses.
            </div>

            <div class="signatures">
                <div>Customer Signature: <div class="sign-line" style="margin-top: 25px;">Agreed to diagnostic terms</div></div>
                <div>Authorized Staff: <div class="sign-line" style="margin-top: 25px;">8J Technical Personnel</div></div>
            </div>
        </div>

        <!-- COPY 2: SHOP / COUNTER COPY -->
        <div class="ticket" style="border-style: dotted;">
            <div class="header">
                <div>
                    <div class="brand-title">8J CELLPHONE REPAIR - SHOP COUNTER COPY</div>
                    <div class="brand-sub">Internal Technician Routing Slip</div>
                </div>
                <div class="ticket-number"><?= htmlspecialchars($job['job_order_no']) ?></div>
            </div>

            <div class="grid-2">
                <div>
                    <div><strong>Customer:</strong> <?= htmlspecialchars($job['customer_name']) ?> (<?= htmlspecialchars($job['customer_contact']) ?>)</div>
                    <div><strong>Device:</strong> <?= htmlspecialchars($job['device_brand'] . ' ' . $job['device_model']) ?></div>
                </div>
                <div>
                    <div><strong>Screen Passcode:</strong> <code><?= htmlspecialchars($job['passcode_or_pattern'] ?: 'None provided') ?></code></div>
                    <div><strong>Technician:</strong> <?= htmlspecialchars($job['tech_name'] ?? 'Pending') ?></div>
                </div>
            </div>

            <div style="margin-top: 10px; font-size: 12px;">
                <strong>Symptoms:</strong> <?= htmlspecialchars($job['reported_issues']) ?>
            </div>
        </div>
    </div>
</body>
</html>
