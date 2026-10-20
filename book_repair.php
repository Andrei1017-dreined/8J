<?php
$page_title = "Book Repair Appointment - 8J Cellphone & Accessories";
require_once __DIR__ . '/config/db.php';

$message = '';
$ticket_issued = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_repair') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $customer_contact = sanitize($_POST['customer_contact'] ?? '');
    $device_brand = sanitize($_POST['device_brand'] ?? '');
    $device_model = sanitize($_POST['device_model'] ?? '');
    $scheduled_date = sanitize($_POST['scheduled_date'] ?? date('Y-m-d'));
    $time_slot = sanitize($_POST['time_slot'] ?? 'Afternoon (1:00 PM - 5:00 PM)');
    $reported_issues = sanitize($_POST['reported_issues'] ?? '');

    $full_issues = $reported_issues . " [Time Slot: " . $time_slot . "]";
    $job_order_no = '8J-REP-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(2)));

    try {
        $stmt = $pdo->prepare("INSERT INTO repair_job_orders 
            (job_order_no, customer_name, customer_contact, device_brand, device_model, reported_issues, scheduled_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')");
        
        $stmt->execute([$job_order_no, $customer_name, $customer_contact, $device_brand, $device_model, $full_issues, $scheduled_date]);

        $ticket_issued = [
            'ticket_no' => $job_order_no,
            'customer_name' => $customer_name,
            'device' => "{$device_brand} {$device_model}",
            'date' => $scheduled_date,
            'slot' => $time_slot
        ];
        $message = "Your repair service appointment has been scheduled successfully!";

    } catch (Exception $e) {
        $message = "Error scheduling repair: " . $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Light Pink Brand Pills & Chips */
    .brand-pill {
        border: 1px solid #fbcfe8 !important;
        background: #fff0f5 !important;
        color: #9d174d !important;
        padding: 0.45rem 0.95rem;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
        box-shadow: 0 1px 3px rgba(244, 114, 182, 0.12);
    }
    .brand-pill i {
        color: #db2777 !important;
        transition: color 0.2s ease;
    }
    .brand-pill:hover {
        background: #fce7f3 !important;
        border-color: #f472b6 !important;
        color: #831843 !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(244, 114, 182, 0.25);
    }
    .brand-pill:hover i {
        color: #be185d !important;
    }
    .brand-pill.active {
        background: #f472b6 !important;
        color: #ffffff !important;
        border-color: #f472b6 !important;
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.45) !important;
    }
    .brand-pill.active i {
        color: #ffffff !important;
    }
    .model-chip {
        border: 1px dashed #fbcfe8;
        background: #fff5f8;
        color: #831843;
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-block;
    }
    .model-chip:hover {
        background: #f472b6;
        color: #ffffff;
        border-style: solid;
        border-color: #f472b6;
    }
    .issue-badge {
        border: 1px solid #fbcfe8 !important;
        background: #fff0f5 !important;
        color: #9d174d !important;
        padding: 0.45rem 0.95rem;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.1s ease;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        user-select: none;
        box-shadow: 0 1px 3px rgba(244, 114, 182, 0.12);
    }
    .issue-badge i {
        color: #db2777 !important;
        font-size: 0.82rem;
        transition: color 0.18s ease;
    }
    .issue-badge:hover {
        border-color: #f472b6 !important;
        background: #fce7f3 !important;
        color: #831843 !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(244, 114, 182, 0.25);
    }
    .issue-badge:hover i {
        color: #be185d !important;
    }
    .issue-badge:active {
        transform: scale(0.97);
    }
    .issue-badge.selected {
        background: #f472b6 !important;
        color: #ffffff !important;
        border-color: #f472b6 !important;
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.45) !important;
        transform: translateY(0);
    }
    .issue-badge.selected i {
        color: #ffffff !important;
    }
    .time-slot-card {
        border: 1px solid #fce7f3;
        background: #ffffff;
        border-radius: 10px;
        padding: 0.75rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }
    .time-slot-card:hover {
        border-color: #f472b6;
        background: #fdf2f8;
    }
    .time-slot-card.active {
        background: #f472b6 !important;
        border-color: #f472b6 !important;
        box-shadow: 0 4px 14px rgba(244, 114, 182, 0.4) !important;
    }
    .time-slot-card.active .fw-bold {
        color: #ffffff !important;
    }
    .time-slot-card.active .text-muted,
    .time-slot-card.active small {
        color: #000000 !important;
        font-weight: 700 !important;
        opacity: 1 !important;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-dark text-white' ?> alert-dismissible fade show mb-4 rounded-3" style="background:#2d1122; border:1px solid #831843;" role="alert">
                    <i class="fa-solid <?= strpos($message, 'Error') !== false ? 'fa-circle-xmark' : 'fa-circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($ticket_issued): ?>
                <!-- Success Ticket Card -->
                <div class="card card-custom p-4 p-md-5 text-center border shadow-sm">
                    <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 80px; height: 80px; object-fit: contain; margin: 0 auto 15px;" alt="8J Logo">
                    <span class="badge bg-dark text-white px-3 py-2 rounded-pill fw-bold text-uppercase mb-2 mx-auto" style="width: fit-content; font-size: 0.75rem; letter-spacing: 0.5px;">Appointment Confirmed</span>
                    <h3 class="fw-extrabold text-dark mb-1">Repair Job Ticket Issued</h3>
                    <p class="text-muted small">Please bring your unit to <strong>8J Cellphone & Accessories</strong> in Tanauan City, Batangas.</p>

                    <div class="p-4 bg-light rounded-4 border my-3 mx-auto" style="max-width: 440px;">
                        <span class="text-muted small text-uppercase fw-bold d-block" style="letter-spacing: 1px;">Job Order Tracking Ticket</span>
                        <h2 class="fw-extrabold text-dark font-monospace my-2 letter-spacing-1"><?= $ticket_issued['ticket_no'] ?></h2>
                        <div class="small text-muted border-top pt-2 mt-2">
                            Device: <strong class="text-dark"><?= htmlspecialchars($ticket_issued['device']) ?></strong><br>
                            Schedule: <strong class="text-dark"><?= date('F d, Y', strtotime($ticket_issued['date'])) ?></strong> (<?= htmlspecialchars($ticket_issued['slot']) ?>)
                        </div>
                    </div>

                    <p class="small text-muted mb-4">
                        You can track the diagnostic and repair stage of your smartphone online in real-time.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>track_repair.php?ticket=<?= urlencode($ticket_issued['ticket_no']) ?>" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Track Ticket Online
                        </a>
                        <a href="<?= BASE_URL ?>print_job_order.php?ticket=<?= urlencode($ticket_issued['ticket_no']) ?>" target="_blank" class="btn btn-outline-dark rounded-pill px-4">
                            <i class="fa-solid fa-print me-1"></i> Print Job Order Stub
                        </a>
                        <a href="<?= BASE_URL ?>" class="btn btn-link text-muted text-decoration-none">Back to Home</a>
                    </div>
                </div>
            <?php else: ?>

                <!-- Two-Column Interactive Appointment Setter -->
                <div class="row g-4">
                    <!-- Form Column -->
                    <div class="col-lg-7">
                        <div class="card card-custom p-4 p-md-5">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <img src="<?= BASE_URL ?>assets/img/logo.png" style="width: 50px; height: 50px; object-fit: contain;" alt="8J Logo">
                                <div>
                                    <h4 class="fw-extrabold mb-0">Repair Service Booking</h4>
                                    <p class="text-muted small mb-0">Fast diagnosis, certified LCD/Battery renewal, & 30-day warranty</p>
                                </div>
                            </div>

                            <form method="POST" action="" id="appointmentForm">
                                <input type="hidden" name="action" value="book_repair">
                                <input type="hidden" name="time_slot" id="selectedTimeSlot" value="Afternoon (1:00 PM - 5:00 PM)">

                                <!-- Customer Details -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Customer Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" id="inputName" class="form-control" placeholder="e.g. Maria Santos" value="<?= htmlspecialchars($current_user['fullname'] ?? '') ?>" required oninput="updateLiveSummary()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Contact Mobile Number <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_contact" id="inputContact" class="form-control" placeholder="09xxxxxxxxx" value="<?= htmlspecialchars($current_user['contact_number'] ?? '') ?>" required oninput="updateLiveSummary()">
                                    </div>
                                </div>

                                <!-- Smartphone Brand (Input + Listed Brand Pills below) -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold d-flex justify-content-between">
                                        <span>Smartphone Brand <span class="text-danger">*</span></span>
                                        <span class="text-muted fw-normal" style="font-size:0.75rem;">Type or pick below</span>
                                    </label>
                                    <input type="text" name="device_brand" id="inputBrand" class="form-control" placeholder="e.g. Apple, Samsung, Xiaomi..." required oninput="onBrandType()">

                                    <!-- Listed Phone Brands Grid -->
                                    <div class="mt-2 pt-1">
                                        <small class="text-muted fw-semibold d-block mb-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Select Brand:</small>
                                        <div class="d-flex flex-wrap gap-2" id="brandPillsContainer">
                                            <span class="brand-pill" data-brand="Apple" onclick="selectBrand('Apple')"><i class="fa-brands fa-apple"></i> Apple (iPhone)</span>
                                            <span class="brand-pill" data-brand="Samsung" onclick="selectBrand('Samsung')"><i class="fa-solid fa-mobile-screen"></i> Samsung</span>
                                            <span class="brand-pill" data-brand="Xiaomi" onclick="selectBrand('Xiaomi')"><i class="fa-solid fa-bolt"></i> Xiaomi / Poco</span>
                                            <span class="brand-pill" data-brand="Oppo" onclick="selectBrand('Oppo')"><i class="fa-solid fa-circle-dot"></i> Oppo</span>
                                            <span class="brand-pill" data-brand="Vivo" onclick="selectBrand('Vivo')"><i class="fa-solid fa-v"></i> Vivo</span>
                                            <span class="brand-pill" data-brand="Realme" onclick="selectBrand('Realme')"><i class="fa-solid fa-r"></i> Realme</span>
                                            <span class="brand-pill" data-brand="Infinix" onclick="selectBrand('Infinix')"><i class="fa-solid fa-fire"></i> Infinix / Tecno</span>
                                            <span class="brand-pill" data-brand="Huawei" onclick="selectBrand('Huawei')"><i class="fa-solid fa-shield"></i> Huawei / Honor</span>
                                            <span class="brand-pill" data-brand="Google" onclick="selectBrand('Google')"><i class="fa-brands fa-google"></i> Google Pixel</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Smartphone Model (Input + Reactive Suggestions) -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Device Model Name <span class="text-danger">*</span></label>
                                    <input type="text" name="device_model" id="inputModel" class="form-control" placeholder="e.g. iPhone 13, Galaxy S22, Redmi Note 12..." required oninput="updateLiveSummary()">
                                    
                                    <!-- Dynamic Model Suggestions based on Brand -->
                                    <div class="mt-2" id="modelSuggestionsContainer" style="display: none;">
                                        <small class="text-muted fw-semibold d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase;">Popular Models for <span id="currentBrandLabel" class="text-dark fw-bold">Brand</span>:</small>
                                        <div class="d-flex flex-wrap gap-2" id="modelsList">
                                            <!-- Injected reactively via JS -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Common Repair Symptoms Chips -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold d-flex justify-content-between">
                                        <span>Common Repair Symptoms</span>
                                        <span class="text-muted fw-normal" style="font-size:0.75rem;">Click to add to description</span>
                                    </label>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="issue-badge" data-issue="Broken LCD / Touch Screen Replacement" onclick="toggleIssue(this, 'Broken LCD / Touch Screen Replacement')">
                                            <i class="fa-solid fa-mobile-screen"></i> LCD / Touch Screen
                                        </span>
                                        <span class="issue-badge" data-issue="Battery Draining Quickly / Replacement" onclick="toggleIssue(this, 'Battery Draining Quickly / Replacement')">
                                            <i class="fa-solid fa-battery-half"></i> Battery Replacement
                                        </span>
                                        <span class="issue-badge" data-issue="Loose Charging Port / Not Charging" onclick="toggleIssue(this, 'Loose Charging Port / Not Charging')">
                                            <i class="fa-solid fa-bolt"></i> Charging Port Problem
                                        </span>
                                        <span class="issue-badge" data-issue="Water / Liquid Damage Cleaning" onclick="toggleIssue(this, 'Water / Liquid Damage Cleaning')">
                                            <i class="fa-solid fa-droplet"></i> Water Damage Diagnostic
                                        </span>
                                        <span class="issue-badge" data-issue="Cracked Rear Camera Lens / Blurry" onclick="toggleIssue(this, 'Cracked Rear Camera Lens / Blurry')">
                                            <i class="fa-solid fa-camera"></i> Camera Lens / Glass
                                        </span>
                                        <span class="issue-badge" data-issue="Ear Speaker / Microphone Audio Issue" onclick="toggleIssue(this, 'Ear Speaker / Microphone Audio Issue')">
                                            <i class="fa-solid fa-volume-high"></i> Speaker / Mic Audio
                                        </span>
                                        <span class="issue-badge" data-issue="Bootloop / Stuck on Logo / OS Re-flash" onclick="toggleIssue(this, 'Bootloop / Stuck on Logo / OS Re-flash')">
                                            <i class="fa-solid fa-arrows-rotate"></i> Bootloop / Software
                                        </span>
                                        <span class="issue-badge" data-issue="Cracked Back Glass / Housing" onclick="toggleIssue(this, 'Cracked Back Glass / Housing')">
                                            <i class="fa-solid fa-shield-halved"></i> Back Glass Replacement
                                        </span>
                                    </div>

                                    <textarea name="reported_issues" id="inputIssues" class="form-control" rows="3" placeholder="Describe symptoms or additional details (e.g. dropped on floor, display is black with vibration, battery drops from 50% to zero)" required oninput="syncBadgesFromTextarea(); updateLiveSummary();"></textarea>
                                </div>

                                <!-- Preferred Date & Time Window -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Preferred Drop-Off Date <span class="text-danger">*</span></label>
                                    <input type="date" name="scheduled_date" id="inputDate" class="form-control mb-3" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required onchange="updateLiveSummary()">

                                    <label class="form-label small fw-bold">Select Preferred Time Window</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <div class="time-slot-card" onclick="selectSlot(this, 'Morning (9:00 AM - 12:00 PM)')">
                                                <div class="fw-bold small">Morning</div>
                                                <small class="text-muted" style="font-size:0.7rem;">9:00 AM - 12:00 PM</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="time-slot-card active" onclick="selectSlot(this, 'Afternoon (1:00 PM - 5:00 PM)')">
                                                <div class="fw-bold small">Afternoon</div>
                                                <small class="text-muted" style="font-size:0.7rem;">1:00 PM - 5:00 PM</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="time-slot-card" onclick="selectSlot(this, 'Evening (5:00 PM - 7:00 PM)')">
                                                <div class="fw-bold small">Evening</div>
                                                <small class="text-muted" style="font-size:0.7rem;">5:00 PM - 7:00 PM</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm" style="font-size: 0.95rem; letter-spacing: 0.3px;">
                                    <i class="fa-solid fa-calendar-check me-2"></i> Confirm Appointment & Generate Job Ticket
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Live Reactive Summary Side Column -->
                    <div class="col-lg-5">
                        <div class="card card-custom p-4 sticky-top shadow-sm" style="top: 85px;">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                <div>
                                    <span class="badge bg-dark text-white px-2 py-1 rounded" style="font-size: 0.68rem;">LIVE SUMMARY</span>
                                    <h5 class="fw-bold mb-0 mt-1">Appointment Slip</h5>
                                </div>
                                <img src="<?= BASE_URL ?>assets/img/icons/support.png" style="width: 32px; height: 32px; object-fit: contain;" alt="Support">
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="small text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Device to Repair:</div>
                                <h5 class="fw-extrabold text-dark mb-0" id="liveDeviceLabel">Select Brand & Model</h5>
                                <div class="small text-muted mt-1" id="liveCustomerLabel">Customer: Walk-in Client</div>
                            </div>

                            <div class="list-group list-group-flush small mb-3">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Target Drop-Off:</span>
                                    <strong id="liveDateLabel" class="text-dark"><?= date('F d, Y') ?></strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Time Window:</span>
                                    <strong id="liveSlotLabel" class="text-dark">Afternoon (1:00 PM - 5:00 PM)</strong>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Location:</span>
                                    <span class="fw-semibold text-dark">Tanauan City, Batangas</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Service Warranty:</span>
                                    <span class="badge bg-dark text-white">30-Day Guaranteed</span>
                                </div>
                            </div>

                            <div class="p-3 bg-white rounded-3 border mb-3">
                                <span class="small text-muted fw-bold d-block text-uppercase mb-1" style="font-size:0.68rem;">Symptoms Preview:</span>
                                <p class="mb-0 small text-dark fst-italic" id="liveIssuesPreview" style="min-height: 40px;">No symptoms selected yet.</p>
                            </div>

                            <div class="p-3 rounded-3 bg-light border small text-muted">
                                <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-shield-halved me-1"></i>Official 8J Repair Policy:</div>
                                Free initial diagnostic assessment. Replacement parts are OEM Grade A with full functional testing before release.
                            </div>

                            <div class="mt-3">
                                <a href="https://www.facebook.com/profile.php?id=100063760157450" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm rounded-pill w-100 py-2 d-inline-flex align-items-center justify-content-center gap-2" style="font-weight:600;">
                                    <i class="fa-brands fa-facebook-messenger" style="color: #0084ff; font-size: 1rem;"></i>
                                    <span>Inquire on Facebook Messenger</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.68rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Database of popular models per brand for reactive auto-fill
const brandModelsDatabase = {
    'Apple': [
        'iPhone 15 Pro Max', 'iPhone 15 Pro', 'iPhone 15', 'iPhone 14 Pro Max', 
        'iPhone 14', 'iPhone 13 Pro', 'iPhone 13', 'iPhone 12', 'iPhone 11', 'iPhone XR', 'iPhone X', 'iPad 9th/10th Gen'
    ],
    'Samsung': [
        'Galaxy S24 Ultra', 'Galaxy S23 5G', 'Galaxy S22', 'Galaxy S21 FE', 
        'Galaxy A54 5G', 'Galaxy A34', 'Galaxy A14', 'Galaxy Z Flip 5', 'Galaxy Note 20 Ultra'
    ],
    'Xiaomi': [
        'Redmi Note 13 Pro', 'Redmi Note 12', 'Poco X6 Pro', 'Poco F5', 
        'Poco M5', 'Xiaomi 13T', 'Redmi 12C', 'Mi 11 Lite'
    ],
    'Oppo': [
        'Reno 11 5G', 'Reno 10 Pro', 'Reno 8', 'Oppo A78', 
        'Oppo A58', 'Oppo A18', 'Find X5 Pro'
    ],
    'Vivo': [
        'Vivo V30 5G', 'Vivo V29e', 'Vivo V27', 'Vivo Y36', 
        'Vivo Y27', 'Vivo Y17s', 'Vivo T2x'
    ],
    'Realme': [
        'Realme 12 Pro+', 'Realme 11 5G', 'Realme C55', 'Realme C53', 
        'Realme C35', 'Realme GT Neo 3', 'Realme 10'
    ],
    'Infinix': [
        'Infinix Note 30 5G', 'Infinix Hot 40 Pro', 'Infinix Hot 30i', 
        'Infinix Smart 8', 'Tecno Spark 20', 'Tecno Camon 20 Pro'
    ],
    'Huawei': [
        'Huawei P60 Pro', 'Nova 11i', 'Nova 10 Pro', 'Nova 9', 
        'Honor 90 5G', 'Honor X9b', 'Honor X8a'
    ],
    'Google': [
        'Pixel 8 Pro', 'Pixel 8', 'Pixel 7 Pro', 'Pixel 7', 'Pixel 6a', 'Pixel 6'
    ]
};

// 1. Reactive Brand Selection (Pill clicked)
function selectBrand(brand) {
    document.getElementById('inputBrand').value = brand;

    // Highlight active pill
    document.querySelectorAll('.brand-pill').forEach(pill => {
        if (pill.dataset.brand.toLowerCase() === brand.toLowerCase()) {
            pill.classList.add('active');
        } else {
            pill.classList.remove('active');
        }
    });

    renderModelSuggestions(brand);
    updateLiveSummary();
}

// 2. Reactive typing in Brand input
function onBrandType() {
    const val = document.getElementById('inputBrand').value.trim().toLowerCase();
    
    // Check if typed brand matches any pill
    let matchedBrand = null;
    document.querySelectorAll('.brand-pill').forEach(pill => {
        const b = pill.dataset.brand.toLowerCase();
        if (val && (b.includes(val) || val.includes(b))) {
            pill.style.display = 'inline-flex';
            if (b === val) {
                pill.classList.add('active');
                matchedBrand = pill.dataset.brand;
            } else {
                pill.classList.remove('active');
            }
        } else if (!val) {
            pill.style.display = 'inline-flex';
            pill.classList.remove('active');
        } else {
            pill.style.display = 'none';
        }
    });

    // If an exact or close brand match is found, show models
    for (const key in brandModelsDatabase) {
        if (key.toLowerCase().includes(val) && val.length >= 2) {
            renderModelSuggestions(key);
            break;
        }
    }

    if (!val) {
        document.getElementById('modelSuggestionsContainer').style.display = 'none';
    }

    updateLiveSummary();
}

// 3. Render model suggestions chips
function renderModelSuggestions(brandName) {
    const container = document.getElementById('modelSuggestionsContainer');
    const label = document.getElementById('currentBrandLabel');
    const list = document.getElementById('modelsList');

    const models = brandModelsDatabase[brandName] || [];
    if (models.length === 0) {
        container.style.display = 'none';
        return;
    }

    label.innerText = brandName;
    list.innerHTML = '';
    models.forEach(model => {
        const span = document.createElement('span');
        span.className = 'model-chip';
        span.innerText = model;
        span.onclick = function() {
            document.getElementById('inputModel').value = model;
            updateLiveSummary();
        };
        list.appendChild(span);
    });

    container.style.display = 'block';
}

// 4. Toggle common issues chips
function toggleIssue(badge, issueText) {
    const textarea = document.getElementById('inputIssues');
    let current = textarea.value.trim();

    if (badge.classList.contains('selected')) {
        badge.classList.remove('selected');
        // Remove issue text from textarea cleanly
        current = current.replace(issueText, '').replace(/,\s*,/g, ',').replace(/^,\s*/, '').replace(/,\s*$/, '').trim();
        textarea.value = current;
    } else {
        badge.classList.add('selected');
        if (current.length > 0 && !current.includes(issueText)) {
            textarea.value = current + ', ' + issueText;
        } else if (!current.includes(issueText)) {
            textarea.value = issueText;
        }
    }
    updateLiveSummary();
}

function syncBadgesFromTextarea() {
    const current = document.getElementById('inputIssues').value;
    document.querySelectorAll('.issue-badge').forEach(b => {
        const issue = b.dataset.issue;
        if (issue && current.includes(issue)) {
            b.classList.add('selected');
        } else if (issue) {
            b.classList.remove('selected');
        }
    });
}

// 5. Select Time Slot
function selectSlot(card, slotValue) {
    document.querySelectorAll('.time-slot-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    document.getElementById('selectedTimeSlot').value = slotValue;
    document.getElementById('liveSlotLabel').innerText = slotValue;
}

// 6. Reactive Live Summary
function updateLiveSummary() {
    const name = document.getElementById('inputName').value.trim() || 'Walk-in Client';
    const contact = document.getElementById('inputContact').value.trim();
    const brand = document.getElementById('inputBrand').value.trim();
    const model = document.getElementById('inputModel').value.trim();
    const issues = document.getElementById('inputIssues').value.trim();
    const dateVal = document.getElementById('inputDate').value;

    // Customer
    document.getElementById('liveCustomerLabel').innerText = 'Customer: ' + name + (contact ? ' (' + contact + ')' : '');

    // Device
    if (brand || model) {
        document.getElementById('liveDeviceLabel').innerText = (brand + ' ' + model).trim();
    } else {
        document.getElementById('liveDeviceLabel').innerText = 'Select Brand & Model';
    }

    // Issues
    if (issues) {
        document.getElementById('liveIssuesPreview').innerText = issues;
    } else {
        document.getElementById('liveIssuesPreview').innerText = 'No symptoms selected yet.';
    }

    // Date
    if (dateVal) {
        const d = new Date(dateVal + 'T00:00:00');
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('liveDateLabel').innerText = d.toLocaleDateString('en-US', options);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateLiveSummary();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
