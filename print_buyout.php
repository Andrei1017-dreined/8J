<?php
/**
 * 8J Cellphone Accessories - Printable Unit Buyout Voucher & Deed of Sale
 * Anti-Fencing Law (P.D. 1612) Compliance Form
 */
require_once __DIR__ . '/config/db.php';
require_login();

$buyout_id = (int)($_GET['id'] ?? 0);
$trans_no = sanitize($_GET['trans'] ?? '');

$stmt = $pdo->prepare("SELECT b.*, 
                              u.brand, u.model_name, u.storage_capacity, u.color, u.imei_1, u.unit_condition,
                              staff.fullname as staff_name
                       FROM unit_buyouts b
                       LEFT JOIN phone_units u ON b.phone_unit_id = u.id
                       LEFT JOIN users staff ON b.handled_by_staff_id = staff.id
                       WHERE b.id = ? OR b.transaction_no = ?");
$stmt->execute([$buyout_id, $trans_no]);
$buyout = $stmt->fetch();

if (!$buyout) {
    die("<h3>Buyout transaction record not found.</h3>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deed of Sale <?= htmlspecialchars($buyout['transaction_no']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 25px; color: #1e293b; }
        .contract-box { max-width: 720px; margin: 0 auto; background: #fff; padding: 35px; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: 800; color: #09090b; margin-bottom: 2px; }
        .sub { font-size: 11px; color: #71717a; }
        .doc-title { font-size: 14px; font-weight: bold; color: #09090b; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 13px; margin-bottom: 20px; }
        .info-card { background: #f8fafc; padding: 12px 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px; }
        .info-label { font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .payout-box { background: #f4f4f5; border: 1px solid #18181b; padding: 15px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .legal-declaration { font-size: 11px; color: #475569; line-height: 1.5; border: 1px solid #cbd5e1; padding: 12px; border-radius: 6px; background: #f8fafc; margin-bottom: 30px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; font-size: 12px; }
        .sign-col { width: 230px; text-align: center; }
        .sign-line { border-top: 1px solid #000; margin-top: 45px; padding-top: 5px; }
        .btn-print { background: #09090b; color: #fff; border: 1px solid #09090b; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-bottom: 20px; transition: background 0.2s ease; }
        .btn-print:hover { background: #27272a; }
        @media print { body { background: #fff; padding: 0; } .btn-print { display: none; } .contract-box { border: none; box-shadow: none; width: 100%; padding: 0; } }
    </style>
</head>
<body>
    <div style="max-width: 720px; margin: 0 auto;">
        <button class="btn-print" onclick="window.print()">Print Buyout Voucher & Deed of Sale</button>

        <div class="contract-box">
            <div class="header text-center" style="text-align: center;">
                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 6px;" alt="8J Logo">
                <div class="title">8J CELLPHONE ACCESSORIES</div>
                <div class="sub">Tanauan City, Batangas • Official Electronic Buyout Certificate</div>
                <div class="doc-title">DEED OF SALE & ANTI-FENCING LAW DECLARATION</div>
                <div style="font-size: 11px; color: #71717a; margin-top: 3px;">In compliance with Presidential Decree No. 1612 (Anti-Fencing Law of 1979)</div>
            </div>

            <div class="grid-2">
                <div class="info-card">
                    <div class="info-label">Seller Identification</div>
                    <div><strong>Name:</strong> <?= htmlspecialchars($buyout['seller_name']) ?></div>
                    <div><strong>Contact:</strong> <?= htmlspecialchars($buyout['seller_contact']) ?></div>
                    <div><strong>Address:</strong> <?= htmlspecialchars($buyout['seller_address']) ?></div>
                    <div style="margin-top: 6px;"><strong>ID Type:</strong> <?= htmlspecialchars($buyout['valid_id_type']) ?></div>
                    <div><strong>ID Number:</strong> <code><?= htmlspecialchars($buyout['valid_id_number']) ?></code></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Acquired Smartphone Unit</div>
                    <div><strong>Device:</strong> <?= htmlspecialchars($buyout['brand'] . ' ' . $buyout['model_name'] . ' ' . $buyout['storage_capacity']) ?></div>
                    <div><strong>Condition:</strong> <?= htmlspecialchars($buyout['unit_condition']) ?></div>
                    <div><strong>Hardware IMEI:</strong> <code><?= htmlspecialchars($buyout['imei_1']) ?></code></div>
                    <div style="margin-top: 6px;"><strong>Transaction #:</strong> <code><?= htmlspecialchars($buyout['transaction_no']) ?></code></div>
                    <div><strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($buyout['created_at'])) ?></div>
                </div>
            </div>

            <div class="payout-box">
                <div>
                    <span style="font-size: 12px; color: #92400e; font-weight: bold; text-transform: uppercase;">Total Payout Disbursed to Seller</span>
                    <div style="font-size: 11px; color: #78350f;">Mode of Payment: <strong><?= strtoupper($buyout['payment_method']) ?></strong></div>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: #92400e;">
                    ₱<?= number_format($buyout['amount_paid'], 2) ?>
                </div>
            </div>

            <div class="legal-declaration">
                <strong>SWORN CERTIFICATION OF LAWFUL OWNERSHIP:</strong><br>
                I, the undersigned Seller, hereby declare and certify under penalty of law that I am the absolute legal and rightful owner of the mobile phone described above. I certify that this device is free from any liens, encumbrances, or cloud on title, and was <strong>NOT</strong> obtained through theft, robbery, fencing, or any unlawful means. By signing below, I voluntarily transfer full ownership, possession, and rights of this unit to <strong>8J Cellphone Accessories</strong> for the agreed monetary consideration.
            </div>

            <div class="signatures">
                <div class="sign-col">
                    <div class="sign-line">Seller's Signature</div>
                    <small><strong><?= htmlspecialchars($buyout['seller_name']) ?></strong></small>
                </div>
                <div class="sign-col">
                    <div class="sign-line">Authorized Purchasing Staff</div>
                    <small><?= htmlspecialchars($buyout['staff_name'] ?? 'Staff') ?> (8J Cellphone Accessories)</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
