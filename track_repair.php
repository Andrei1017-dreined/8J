<?php
$page_title = "Track Repair Job Order";
require_once __DIR__ . '/includes/header.php';

// One search box: a job order number OR the mobile number used when booking.
// Older links (QR code on the slip, My Orders, booking confirmation) still send ?ticket= / ?contact=.
$search_raw = trim((string)($_GET['q'] ?? ''));
if ($search_raw === '') $search_raw = trim((string)($_GET['ticket'] ?? ''));
if ($search_raw === '') $search_raw = trim((string)($_GET['contact'] ?? ''));
$search_raw = mb_substr($search_raw, 0, 40);

$job = null;
$jobs = [];          // several repairs on one mobile number
$searched = $search_raw !== '';
$search_type = null; // 'ticket' | 'phone'

// Last 10 digits of a PH mobile, so 0917…, +63917… and 63917… all match each other
function tr_phone_key($s) {
    $d = preg_replace('/\D/', '', (string)$s);
    return strlen($d) >= 10 ? substr($d, -10) : '';
}

if ($searched) {
    $phone_key = tr_phone_key($search_raw);
    $looks_like_phone = preg_match('/^[\d\s()+\-.]+$/', $search_raw) && $phone_key !== '';

    $base_sql = "SELECT r.*, t.fullname as tech_name
                 FROM repair_job_orders r
                 LEFT JOIN users t ON r.technician_id = t.id";

    if ($looks_like_phone) {
        $search_type = 'phone';
        $stmt = $pdo->prepare("$base_sql
            WHERE RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(r.customer_contact, ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), 10) = ?
            ORDER BY r.created_at DESC, r.id DESC
            LIMIT 20");
        $stmt->execute([$phone_key]);
        $jobs = $stmt->fetchAll();
        if (count($jobs) === 1) {
            $job = $jobs[0];
            $jobs = [];
        }
    } else {
        $search_type = 'ticket';
        $ticket = strtoupper(preg_replace('/\s+/', '', $search_raw));
        $stmt = $pdo->prepare("$base_sql WHERE r.job_order_no = ? LIMIT 1");
        $stmt->execute([$ticket]);
        $job = $stmt->fetch() ?: null;
    }
}
$search_query = $search_raw; // shown back in the box

// Progress model
$flow = [
    'scheduled'        => ['Booked',     'fa-calendar-check'],
    'received'         => ['Received',   'fa-inbox'],
    'diagnosing'       => ['Diagnosing', 'fa-stethoscope'],
    'repairing'        => ['Repairing',  'fa-screwdriver-wrench'],
    'ready_for_pickup' => ['Ready',      'fa-bell'],
    'completed'        => ['Released',   'fa-shield-halved'],
];
$status_info = [
    'scheduled'        => ['Booked – waiting for drop-off', 'st-wait'],
    'received'         => ['Received at the store',          'st-progress'],
    'diagnosing'       => ['Being diagnosed',                'st-progress'],
    'waiting_parts'    => ['Waiting for parts',              'st-warn'],
    'repairing'        => ['Being repaired',                 'st-progress'],
    'ready_for_pickup' => ['Ready for pick-up',              'st-ready'],
    'completed'        => ['Released to customer',           'st-done'],
    'unrepairable'     => ['Could not be repaired',          'st-stop'],
    'cancelled'        => ['Cancelled',                      'st-stop'],
];

if ($job) {
    $status = $job['status'];
    $order = array_keys($flow);
    // "Waiting for parts" sits between diagnosing and repairing
    $cur = $status === 'waiting_parts' ? array_search('diagnosing', $order) : array_search($status, $order);
    $stopped = in_array($status, ['unrepairable', 'cancelled'], true);
    if ($cur === false) $cur = 0;

    // The booking form appends " [Time Slot: ...]" to the problem description
    $issues = $job['reported_issues'];
    $slot = '';
    if (preg_match('/\s*\[Time Slot:\s*([^\]]+)\]\s*$/', $issues, $m)) {
        $slot = $m[1];
        $issues = trim(substr($issues, 0, -strlen($m[0])));
    }

    $total = (float)$job['final_cost'] > 0 ? (float)$job['final_cost'] : (float)$job['estimated_cost'];
    $is_final = (float)$job['final_cost'] > 0;
    $deposit = (float)$job['deposit_paid'];
    $balance = max(0, $total - $deposit);

    $warranty_days_left = null;
    if (!empty($job['warranty_expiry'])) {
        $warranty_days_left = (int)floor((strtotime($job['warranty_expiry'] . ' 23:59:59') - time()) / 86400);
    }
    [$status_label, $status_class] = $status_info[$status] ?? [ucwords(str_replace('_', ' ', $status)), 'st-wait'];
}
$fb = 'https://www.facebook.com/profile.php?id=100063760157450';
?>

<style>
.tr-hero {
    position: relative; overflow: hidden; border-radius: 24px; color: #fff;
    background: linear-gradient(135deg, #2d1122 0%, #4a1534 60%, #831843 100%);
    padding: clamp(26px, 4vw, 44px);
    box-shadow: 0 24px 50px -30px rgba(131, 24, 67, 0.7);
}
.tr-hero::after {
    content: ''; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
    background: radial-gradient(circle, rgba(244, 114, 182, 0.35), transparent 70%); pointer-events: none;
}
.tr-hero h1 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; letter-spacing: -0.02em; }
.tr-hero p { color: rgba(255,255,255,0.72); }
.tr-form .form-control {
    height: 52px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.1); color: #fff; font-family: ui-monospace, Menlo, Consolas, monospace;
}
.tr-form .form-control::placeholder { color: rgba(255,255,255,0.5); }
.tr-form .form-control:focus { background: rgba(255,255,255,0.16); border-color: #f9a8d4 !important; color: #fff; box-shadow: 0 0 0 3px rgba(249,168,212,0.25) !important; }
.tr-form label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #fbcfe8; }
.tr-form .btn { height: 52px; border-radius: 14px; }
.tr-card { background: #fff; border: 1px solid #fce7f3; border-radius: 22px; box-shadow: 0 18px 40px -30px rgba(131,24,67,0.35); }
.tr-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; }
.tr-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
.st-wait { background: #f4f4f5; color: #52525b; }
.st-progress { background: #fdf2f8; color: #be185d; }
.st-warn { background: #fffbeb; color: #b45309; }
.st-ready { background: #ecfdf5; color: #047857; }
.st-done { background: #2d1122; color: #fbcfe8; }
.st-stop { background: #fef2f2; color: #b91c1c; }

/* Progress timeline: horizontal on desktop, vertical on phones */
.tr-steps { display: flex; position: relative; margin: 8px 0 4px; }
.tr-step { flex: 1; text-align: center; position: relative; z-index: 1; }
.tr-step .dot {
    width: 42px; height: 42px; margin: 0 auto 8px; border-radius: 50%;
    display: grid; place-items: center; font-size: 0.95rem;
    background: #fff; color: #d4a5bd; border: 2px solid #fbcfe8; transition: all .3s;
}
.tr-step .lbl { font-size: 0.78rem; font-weight: 700; color: #a1849a; }
.tr-step.done .dot { background: #db2777; border-color: #db2777; color: #fff; }
.tr-step.done .lbl { color: #2b1120; }
.tr-step.now .dot { box-shadow: 0 0 0 6px rgba(219, 39, 119, 0.18); transform: scale(1.08); }
.tr-step.now .lbl { color: #be185d; }
.tr-step.halt .dot { background: #fff7ed; border-color: #f59e0b; color: #b45309; }
.tr-steps .rail, .tr-steps .fill { position: absolute; top: 20px; left: 8.33%; height: 3px; border-radius: 3px; }
.tr-steps .rail { right: 8.33%; background: #fce7f3; }
.tr-steps .fill { background: linear-gradient(90deg, #f472b6, #db2777); }
.tr-steps.stopped .tr-step:not(.done) .dot { opacity: .45; }
@media (max-width: 767.98px) {
    .tr-steps { flex-direction: column; gap: 14px; padding-left: 4px; }
    .tr-step { display: flex; align-items: center; gap: 12px; text-align: left; }
    .tr-step .dot { margin: 0; width: 36px; height: 36px; flex: none; }
    .tr-steps .rail, .tr-steps .fill { top: 18px; left: 21px; width: 3px; height: auto; right: auto; bottom: 18px; }
    .tr-steps .fill { bottom: auto; }
}
.tr-box { background: #fff8fb; border: 1px solid #fce7f3; border-radius: 16px; padding: 16px 18px; height: 100%; }
.tr-box .k { font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #a1849a; margin-bottom: 6px; }
.tr-row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: 0.9rem; border-bottom: 1px dashed #fbcfe8; }
.tr-row:last-child { border-bottom: 0; }
.tr-row span:first-child { color: #6b5563; }
.tr-row strong { color: #2b1120; }
.tr-pick { padding: 14px 18px; border-radius: 16px; border: 1px solid #fce7f3; background: #fff8fb; transition: border-color .2s, transform .2s, box-shadow .2s; }
.tr-pick:hover, .tr-pick:focus-visible { border-color: #f9a8d4; transform: translateY(-1px); box-shadow: 0 10px 24px -18px rgba(131,24,67,0.5); }
.tr-how { counter-reset: how; }
.tr-how .item { display: flex; gap: 14px; align-items: flex-start; }
.tr-how .n { width: 34px; height: 34px; flex: none; border-radius: 12px; background: #fce7f3; color: #be185d; font-weight: 800; display: grid; place-items: center; }
</style>

<div class="container py-4 py-md-5" style="max-width: 980px;">

    <!-- Search -->
    <section class="tr-hero mb-4">
        <div class="position-relative" style="z-index: 2;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;">
                <i class="fa-solid fa-screwdriver-wrench"></i> Repair tracker
            </div>
            <h1 class="mb-2">Track your repair</h1>
            <p class="mb-4" style="max-width: 560px;">Enter <strong class="text-white">either</strong> the job order number from your slip, <strong class="text-white">or</strong> the mobile number you gave us when booking.</p>

            <form method="GET" action="" class="tr-form row g-2 g-md-3 align-items-end">
                <div class="col-md-9">
                    <label for="trQuery" class="mb-1">Job order number or mobile number</label>
                    <input type="text" id="trQuery" name="q" class="form-control" placeholder="8J-REP-2611-A1B2C3  or  09xxxxxxxxx" value="<?= htmlspecialchars($search_query) ?>" required autocomplete="off" spellcheck="false" maxlength="40">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn fw-bold" style="background: #f9a8d4 !important; color: #3b0a24 !important; border: 0 !important;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Track
                    </button>
                </div>
            </form>
        </div>
    </section>

    <?php if ($searched && $jobs): ?>
        <!-- One mobile number, several repairs: let them pick -->
        <div class="tr-card p-4 p-md-5">
            <h2 class="h5 fw-extrabold mb-1"><?= count($jobs) ?> repairs on this mobile number</h2>
            <p class="small text-muted mb-4">Newest first. Tap one to see its progress.</p>
            <div class="d-grid gap-2">
                <?php foreach ($jobs as $j):
                    [$jl, $jc] = $status_info[$j['status']] ?? [ucwords(str_replace('_', ' ', $j['status'])), 'st-wait']; ?>
                    <a href="?q=<?= urlencode($j['job_order_no']) ?>" class="tr-pick d-flex flex-wrap align-items-center justify-content-between gap-2 text-decoration-none">
                        <div>
                            <div class="font-monospace fw-bold" style="color:#2b1120;"><?= htmlspecialchars($j['job_order_no']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars(trim($j['device_brand'] . ' ' . $j['device_model'])) ?> · Booked <?= date('M j, Y', strtotime($j['created_at'])) ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="tr-pill <?= $jc ?>"><?= htmlspecialchars($jl) ?></span>
                            <i class="fa-solid fa-chevron-right" style="color:#db2777;"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($searched && !$job): ?>
        <div class="tr-card p-4 p-md-5 text-center">
            <div class="mx-auto mb-3 d-grid" style="width: 64px; height: 64px; border-radius: 20px; background: #fdf2f8; color: #db2777; place-items: center; font-size: 1.5rem;">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h4 class="fw-extrabold mb-2">We couldn't find that repair</h4>
            <p class="text-muted mb-4 mx-auto" style="max-width: 460px;">
                <?php if ($search_type === 'phone'): ?>
                    No repairs are booked under <span class="font-monospace"><?= htmlspecialchars($search_query) ?></span>. Try the job order number on your slip (it looks like <span class="font-monospace">8J-REP-2611-A1B2C3</span>), or the other number you may have given us.
                <?php else: ?>
                    Check the job order number (it looks like <span class="font-monospace">8J-REP-2611-A1B2C3</span>), or search with the mobile number you gave when booking instead.
                <?php endif; ?>
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= $fb ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill px-4"><i class="fa-brands fa-facebook-messenger me-1"></i> Ask us on Messenger</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>my_orders.php" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-box me-1"></i> See my repairs</a>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($job): ?>
        <!-- Result -->
        <div class="tr-card p-4 p-md-5 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 pb-4 mb-4 border-bottom" style="border-color: #fce7f3 !important;">
                <div>
                    <div class="text-muted small fw-semibold mb-1">Job order</div>
                    <h2 class="h4 fw-extrabold font-monospace mb-2"><?= htmlspecialchars($job['job_order_no']) ?></h2>
                    <span class="tr-pill <?= $status_class ?>"><?= htmlspecialchars($status_label) ?></span>
                </div>
                <div class="text-md-end">
                    <div class="text-muted small fw-semibold mb-1">Device</div>
                    <div class="h5 fw-extrabold mb-1"><?= htmlspecialchars($job['device_brand'] . ' ' . $job['device_model']) ?></div>
                    <div class="small text-muted">Booked <?= date('M j, Y', strtotime($job['created_at'])) ?><?= !empty($job['tech_name']) ? ' · Technician: ' . htmlspecialchars($job['tech_name']) : '' ?></div>
                </div>
            </div>

            <?php
            $total_steps = count($flow);
            $fill_pct = $total_steps > 1 ? ($cur / ($total_steps - 1)) * 83.34 : 0;
            ?>
            <div class="tr-steps <?= $stopped ? 'stopped' : '' ?>" aria-label="Repair progress">
                <span class="rail"></span>
                <span class="fill d-none d-md-block" style="width: <?= $stopped ? 0 : round($fill_pct, 2) ?>%;"></span>
                <?php $i = 0; foreach ($flow as $key => [$label, $icon]):
                    $done = !$stopped && $i <= $cur;
                    $now = !$stopped && $i === $cur;
                    $halt = $status === 'waiting_parts' && $key === 'repairing';
                ?>
                    <div class="tr-step <?= $done ? 'done' : '' ?> <?= $now ? 'now' : '' ?> <?= $halt ? 'halt' : '' ?>" <?= $now ? 'aria-current="step"' : '' ?>>
                        <div class="dot"><i class="fa-solid <?= $halt ? 'fa-box-open' : $icon ?>"></i></div>
                        <div class="lbl"><?= $halt ? 'Waiting for parts' : $label ?></div>
                    </div>
                <?php $i++; endforeach; ?>
            </div>

            <?php if ($status === 'waiting_parts'): ?>
                <div class="alert alert-warning rounded-4 mt-4 mb-0 small"><i class="fa-solid fa-box-open me-1"></i> A replacement part is on order. We'll continue the repair as soon as it arrives and text you when it's ready.</div>
            <?php elseif ($stopped): ?>
                <div class="alert alert-danger rounded-4 mt-4 mb-0 small"><i class="fa-solid fa-circle-info me-1"></i>
                    <?= $status === 'cancelled' ? 'This job order was cancelled.' : 'Our technician could not repair this device.' ?>
                    Please visit the store or message us to collect your phone.</div>
            <?php elseif ($status === 'ready_for_pickup'): ?>
                <div class="alert alert-success rounded-4 mt-4 mb-0 small"><i class="fa-solid fa-bell me-1"></i> Your phone is ready! Bring your job order slip to the 8J counter at Victory Mall Tanauan.</div>
            <?php endif; ?>
        </div>

        <div class="row g-3 g-md-4 mb-4">
            <div class="col-md-6">
                <div class="tr-box">
                    <div class="k">Reported problem</div>
                    <p class="mb-0" style="color: #2b1120;"><?= nl2br(htmlspecialchars($issues)) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tr-box">
                    <div class="k">Technician notes</div>
                    <p class="mb-0 <?= empty($job['diagnostic_notes']) ? 'text-muted fst-italic' : '' ?>" style="<?= empty($job['diagnostic_notes']) ? '' : 'color:#2b1120;' ?>">
                        <?= !empty($job['diagnostic_notes']) ? nl2br(htmlspecialchars($job['diagnostic_notes'])) : 'Our technician will add notes here after the diagnosis.' ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tr-box">
                    <div class="k">Schedule</div>
                    <div class="tr-row"><span>Drop-off date</span><strong><?= !empty($job['scheduled_date']) ? date('M j, Y', strtotime($job['scheduled_date'])) : '—' ?></strong></div>
                    <?php if ($slot): ?><div class="tr-row"><span>Time window</span><strong><?= htmlspecialchars($slot) ?></strong></div><?php endif; ?>
                    <div class="tr-row"><span>Completed</span><strong><?= !empty($job['completed_at']) ? date('M j, Y', strtotime($job['completed_at'])) : 'Not yet' ?></strong></div>
                    <div class="tr-row"><span>Where</span><strong>Victory Mall Tanauan, Stall #S1</strong></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tr-box">
                    <div class="k"><?= $is_final ? 'Final cost' : 'Cost estimate' ?></div>
                    <?php if ($total <= 0): ?>
                        <p class="mb-0 text-muted fst-italic">We'll quote you after the free diagnosis. Nothing is charged without your OK.</p>
                    <?php else: ?>
                        <?php if ((float)$job['parts_cost'] > 0): ?><div class="tr-row"><span>Parts</span><strong><?= format_currency($job['parts_cost']) ?></strong></div><?php endif; ?>
                        <?php if ((float)$job['labor_fee'] > 0): ?><div class="tr-row"><span>Labour</span><strong><?= format_currency($job['labor_fee']) ?></strong></div><?php endif; ?>
                        <div class="tr-row"><span><?= $is_final ? 'Total' : 'Estimated total' ?></span><strong><?= format_currency($total) ?></strong></div>
                        <?php if ($deposit > 0): ?><div class="tr-row"><span>Deposit paid</span><strong>− <?= format_currency($deposit) ?></strong></div><?php endif; ?>
                        <?php if (!in_array($status, ['completed', 'cancelled', 'unrepairable'], true)): ?>
                            <div class="tr-row"><span class="fw-bold" style="color:#2b1120;">Balance due on pick-up</span><strong style="color:#be185d; font-size: 1.05rem;"><?= format_currency($balance) ?></strong></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($warranty_days_left !== null): ?>
            <div class="tr-card p-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-grid" style="width: 48px; height: 48px; border-radius: 14px; place-items: center; background: <?= $warranty_days_left >= 0 ? '#ecfdf5' : '#f4f4f5' ?>; color: <?= $warranty_days_left >= 0 ? '#047857' : '#71717a' ?>; font-size: 1.2rem;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="fw-extrabold"><?= $warranty_days_left >= 0 ? 'Service warranty active' : 'Service warranty ended' ?></div>
                        <div class="small text-muted"><?= $warranty_days_left >= 0 ? 'Valid until ' : 'Ended ' ?><?= date('F j, Y', strtotime($job['warranty_expiry'])) ?></div>
                    </div>
                </div>
                <?php if ($warranty_days_left >= 0): ?>
                    <span class="tr-pill st-ready"><?= $warranty_days_left ?> day<?= $warranty_days_left === 1 ? '' : 's' ?> left</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= $fb ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill px-4"><i class="fa-brands fa-facebook-messenger me-1"></i> Message us about this repair</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>print_job_order.php?ticket=<?= urlencode($job['job_order_no']) ?>" target="_blank" class="btn btn-outline-dark rounded-pill px-4"><i class="fa-solid fa-print me-1"></i> Print job order</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-plus me-1"></i> Book another repair</a>
        </div>

    <?php else: ?>
        <!-- Before searching: how it works -->
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="tr-card p-4 p-md-5 h-100">
                    <h2 class="h5 fw-extrabold mb-4">How repair tracking works</h2>
                    <div class="tr-how d-grid gap-4">
                        <div class="item"><div class="n">1</div><div><div class="fw-bold">Book or drop off your phone</div><div class="small text-muted">You get a job order number like <span class="font-monospace">8J-REP-2611-A1B2C3</span>. Track with that, or with your mobile number.</div></div></div>
                        <div class="item"><div class="n">2</div><div><div class="fw-bold">We diagnose it for free</div><div class="small text-muted">Our technician adds notes and a cost estimate. Nothing is repaired or charged without your OK.</div></div></div>
                        <div class="item"><div class="n">3</div><div><div class="fw-bold">Follow every step here</div><div class="small text-muted">Received, diagnosing, repairing, ready for pick-up. Your service warranty shows here too.</div></div></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="tr-card p-4 p-md-5 h-100 d-flex flex-column">
                    <h2 class="h5 fw-extrabold mb-2">Lost your job order number?</h2>
                    <p class="small text-muted mb-4">Just search with the mobile number you gave when booking. Still can't find it? Message us and we'll look it up for you.</p>
                    <div class="d-grid gap-2 mt-auto">
                        <a href="<?= $fb ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill"><i class="fa-brands fa-facebook-messenger me-1"></i> Message 8J on Facebook</a>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= BASE_URL ?>my_orders.php" class="btn btn-outline-dark rounded-pill"><i class="fa-solid fa-box me-1"></i> See repairs on my account</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>book_repair.php" class="btn btn-primary rounded-pill"><i class="fa-solid fa-calendar-check me-1"></i> Book a repair</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
