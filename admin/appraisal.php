<?php
$page_title = "Smartphone Appraisal Calculator";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff', 'technician');

$message = '';
$appraisal_result = null;

// Fetch matrices for phone models
$matrices = $pdo->query("SELECT * FROM appraisal_matrix WHERE is_active = 1 ORDER BY brand, model_name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_appraisal') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $contact_number = sanitize($_POST['contact_number'] ?? '');
    $matrix_id = (int)($_POST['matrix_id'] ?? 0);
    $imei = sanitize($_POST['imei'] ?? '');
    $cosmetic_grade = sanitize($_POST['cosmetic_grade'] ?? 'Grade A');
    $screen_condition = sanitize($_POST['screen_condition'] ?? 'Flawless');
    $battery_health = (int)($_POST['battery_health'] ?? 100);
    $biometrics_ok = isset($_POST['biometrics_ok']) ? 1 : 0;
    $cameras_ok = isset($_POST['cameras_ok']) ? 1 : 0;
    $speakers_ok = isset($_POST['speakers_ok']) ? 1 : 0;
    $icloud_clean = isset($_POST['icloud_clean']) ? 1 : 0;
    $remarks = sanitize($_POST['remarks'] ?? '');

    // Fetch matrix details
    $stmt = $pdo->prepare("SELECT * FROM appraisal_matrix WHERE id = ?");
    $stmt->execute([$matrix_id]);
    $mat = $stmt->fetch();

    if ($mat && $icloud_clean == 1) {
        $base_val = (float)$mat['base_market_value'];
        
        // Grade multiplier
        $multiplier = 1.0;
        if ($cosmetic_grade === 'Grade B') $multiplier = (float)$mat['grade_b_pct'] / 100.0;
        if ($cosmetic_grade === 'Grade C') $multiplier = (float)$mat['grade_c_pct'] / 100.0;
        $graded_value = $base_val * $multiplier;

        // Deductions
        $deductions = 0.0;
        if ($screen_condition === 'Minor Scratches') {
            $deductions += (float)$mat['screen_scratch_deduction'];
        } elseif ($screen_condition === 'Cracked / Lines') {
            $deductions += (float)$mat['screen_crack_deduction'];
        }

        if ($battery_health < 80) {
            $deductions += (float)$mat['battery_low_deduction'];
        }

        if (!$biometrics_ok) {
            $deductions += (float)$mat['biometrics_issue_deduction'];
        }

        if (!$cameras_ok) {
            $deductions += (float)$mat['camera_issue_deduction'];
        }

        $final_val = max(1000.00, $graded_value - $deductions);

        // Insert appraisal record
        $ins = $pdo->prepare("INSERT INTO appraisal_records 
            (customer_name, contact_number, brand, model_name, storage_capacity, imei, cosmetic_grade, screen_condition, battery_health, face_or_fingerprint_working, cameras_working, speakers_mic_working, icloud_google_clean, initial_market_val, total_deductions, final_appraised_value, technician_id, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $technician_id = !empty($current_user['id']) ? $current_user['id'] : ($_SESSION['user']['id'] ?? 1);
        $ins->execute([
            $customer_name, $contact_number, $mat['brand'], $mat['model_name'], $mat['storage_capacity'], $imei,
            $cosmetic_grade, $screen_condition, $battery_health, $biometrics_ok, $cameras_ok, $speakers_ok, $icloud_clean,
            $base_val, $deductions, $final_val, $technician_id, $remarks
        ]);

        $saved_id = $pdo->lastInsertId();
        $message = "Appraisal recorded successfully! Appraised Valuation: " . format_currency($final_val);
        $appraisal_result = [
            'id' => $saved_id,
            'customer_name' => $customer_name,
            'device' => "{$mat['brand']} {$mat['model_name']} ({$mat['storage_capacity']})",
            'final_value' => $final_val,
            'deductions' => $deductions
        ];
    } else {
        $message = "Error: Cannot appraise device with iCloud / Google Account Lock!";
    }
}

// Fetch recent appraisals
$recent_appraisals = $pdo->query("SELECT * FROM appraisal_records ORDER BY id DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-calculator text-primary me-2"></i>Smartphone Appraisal Calculator</h2>
                    <p class="text-muted small mb-0">Evaluate cosmetic, screen, battery & hardware condition to determine fair trade-in and buying value</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
                    <i class="fa-solid <?= strpos($message, 'Error') !== false ? 'fa-circle-xmark' : 'fa-circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($appraisal_result): ?>
                <div class="card card-custom border-dark mb-4 p-4 bg-white shadow-sm animate-scale-in">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-dark mb-1">Appraisal Complete #<?= $appraisal_result['id'] ?></h4>
                            <p class="mb-0 text-muted">Customer: <strong class="text-dark"><?= htmlspecialchars($appraisal_result['customer_name']) ?></strong> | Unit: <strong class="text-dark"><?= htmlspecialchars($appraisal_result['device']) ?></strong></p>
                            <h2 class="fw-extrabold text-dark mt-2 mb-0 font-monospace"><?= format_currency($appraisal_result['final_value']) ?></h2>
                            <small class="text-muted">Total condition deductions applied: <?= format_currency($appraisal_result['deductions']) ?></small>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-column gap-2">
                            <a href="<?= BASE_URL ?>admin/trading.php?appraisal_id=<?= $appraisal_result['id'] ?>" class="btn btn-primary fw-bold rounded-pill btn-tactile">
                                <i class="fa-solid fa-repeat me-1"></i> Proceed to Trade-In (Swap)
                            </a>
                            <a href="<?= BASE_URL ?>admin/buying.php?appraisal_id=<?= $appraisal_result['id'] ?>" class="btn btn-outline-dark fw-bold rounded-pill btn-tactile">
                                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Buy Out Unit (Cash Purchase)
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Appraisal Calculation Form -->
                <div class="col-lg-7">
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Unit Evaluation Checklist</h5>
                        <form method="POST" action="" id="appraisalForm">
                            <input type="hidden" name="action" value="save_appraisal">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Customer Full Name</label>
                                    <input type="text" name="customer_name" class="form-control" placeholder="e.g. Maria Santos" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" placeholder="0917xxxxxxx" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Select Brand & Model</label>
                                    <select name="matrix_id" id="matrixSelect" class="form-select" required onchange="calculateLive()">
                                        <option value="" disabled selected>-- Choose Phone Model --</option>
                                        <?php foreach ($matrices as $m): ?>
                                            <option value="<?= $m['id'] ?>" 
                                                data-base="<?= $m['base_market_value'] ?>"
                                                data-grade-b="<?= $m['grade_b_pct'] ?>"
                                                data-grade-c="<?= $m['grade_c_pct'] ?>"
                                                data-scratch="<?= $m['screen_scratch_deduction'] ?>"
                                                data-crack="<?= $m['screen_crack_deduction'] ?>"
                                                data-battery="<?= $m['battery_low_deduction'] ?>"
                                                data-camera="<?= $m['camera_issue_deduction'] ?>"
                                                data-biometrics="<?= $m['biometrics_issue_deduction'] ?>">
                                                <?= htmlspecialchars($m['brand'] . ' ' . $m['model_name'] . ' (' . $m['storage_capacity'] . ')') ?> - Base: <?= format_currency($m['base_market_value']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">IMEI / Serial Number</label>
                                    <input type="text" name="imei" class="form-control" placeholder="15 digits" maxlength="18">
                                </div>
                            </div>

                            <!-- Grading & Condition Options -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Cosmetic Grade</label>
                                    <select name="cosmetic_grade" id="cosmeticGrade" class="form-select" onchange="calculateLive()">
                                        <option value="Grade A">Grade A (95%-99% Pristine)</option>
                                        <option value="Grade B">Grade B (Minor wear/scuffs)</option>
                                        <option value="Grade C">Grade C (Heavy scratches/dents)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Screen Glass & Display</label>
                                    <select name="screen_condition" id="screenCondition" class="form-select" onchange="calculateLive()">
                                        <option value="Flawless">Flawless (No scratches)</option>
                                        <option value="Minor Scratches">Minor hairline scratches</option>
                                        <option value="Cracked / Lines">Cracked Glass / Lines / Spot</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Battery Health (%)</label>
                                    <input type="number" name="battery_health" id="batteryHealth" class="form-control" min="50" max="100" value="88" oninput="calculateLive()" required>
                                </div>
                            </div>

                            <!-- Hardware Test Checklist -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <label class="small fw-bold text-dark d-block mb-2 text-uppercase" style="font-size:0.75rem;">
                                    Hardware & Security Test Checklist
                                </label>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="biometrics_ok" id="biometricsCheck" checked onchange="calculateLive()">
                                            <label class="form-check-label small" for="biometricsCheck">Face ID / Fingerprint working</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="cameras_ok" id="camerasCheck" checked onchange="calculateLive()">
                                            <label class="form-check-label small" for="camerasCheck">Front & Rear Cameras clear</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="speakers_ok" id="speakersCheck" checked onchange="calculateLive()">
                                            <label class="form-check-label small" for="speakersCheck">Mic, Speakers, Charging Port OK</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="icloud_clean" id="icloudCheck" checked required>
                                            <label class="form-check-label small fw-bold text-danger" for="icloudCheck">Clean iCloud / Google Bypass</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Technician Observations & Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. 3Utools test score 98%, original housing"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
                                <i class="fa-solid fa-save me-1"></i> Save Appraisal Record
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Live Appraisal Summary Card -->
                <div class="col-lg-5">
                    <div class="card card-custom p-4 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Live Valuation Preview</h5>
                        
                        <div class="p-3 bg-light rounded-3 mb-3 text-center border">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Estimated Appraisal Offer</span>
                            <h2 class="display-6 fw-extrabold text-primary mb-0" id="liveTotal">₱0.00</h2>
                            <small class="text-muted" id="liveModelLabel">Please select a phone model</small>
                        </div>

                        <div class="list-group list-group-flush small mb-3">
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Base Market Benchmark:</span>
                                <strong id="liveBaseVal">₱0.00</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Cosmetic Grade Rate:</span>
                                <strong id="liveGradeRate">100%</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 text-danger">
                                <span>Screen Deduction:</span>
                                <strong id="liveScreenDed">-₱0.00</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 text-danger">
                                <span>Battery Condition Deduction:</span>
                                <strong id="liveBatteryDed">-₱0.00</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 text-danger">
                                <span>Hardware Fault Deductions:</span>
                                <strong id="liveHardwareDed">-₱0.00</strong>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 small mb-0">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            This algorithm standardizes buy-in prices to maintain shop margins and prevent over-valuation.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appraisals Table -->
            <div class="card card-custom mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Appraisal History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Phone Model</th>
                                    <th>Grade</th>
                                    <th>Battery</th>
                                    <th class="text-end">Appraised Value</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_appraisals)): ?>
                                    <?php foreach ($recent_appraisals as $app): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= $app['id'] ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($app['customer_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($app['contact_number']) ?></small>
                                            </td>
                                            <td class="fw-semibold"><?= htmlspecialchars($app['brand'] . ' ' . $app['model_name'] . ' ' . $app['storage_capacity']) ?></td>
                                            <td><span class="badge bg-secondary"><?= $app['cosmetic_grade'] ?></span></td>
                                            <td><?= $app['battery_health'] ?>%</td>
                                            <td class="text-end fw-extrabold text-success"><?= format_currency($app['final_appraised_value']) ?></td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border">
                                                    <?= ucwords(str_replace('_', ' ', $app['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>admin/trading.php?appraisal_id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Trade/Swap">
                                                    <i class="fa-solid fa-repeat"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>admin/buying.php?appraisal_id=<?= $app['id'] ?>" class="btn btn-sm btn-outline-warning text-dark py-0 px-2" title="Buyout">
                                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No appraisal transactions recorded yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function calculateLive() {
    const select = document.getElementById('matrixSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.dataset.base) return;

    const baseVal = parseFloat(opt.dataset.base);
    const gradeBPct = parseFloat(opt.dataset.gradeB) / 100.0;
    const gradeCPct = parseFloat(opt.dataset.gradeC) / 100.0;
    const scratchDed = parseFloat(opt.dataset.scratch);
    const crackDed = parseFloat(opt.dataset.crack);
    const batteryDed = parseFloat(opt.dataset.battery);
    const cameraDed = parseFloat(opt.dataset.camera);
    const biometricsDed = parseFloat(opt.dataset.biometrics);

    document.getElementById('liveModelLabel').innerText = opt.text.split(' - ')[0];
    document.getElementById('liveBaseVal').innerText = '₱' + baseVal.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Cosmetic Grade
    const grade = document.getElementById('cosmeticGrade').value;
    let multiplier = 1.0;
    let rateLabel = '100%';
    if (grade === 'Grade B') { multiplier = gradeBPct; rateLabel = (gradeBPct * 100) + '%'; }
    if (grade === 'Grade C') { multiplier = gradeCPct; rateLabel = (gradeCPct * 100) + '%'; }
    document.getElementById('liveGradeRate').innerText = rateLabel;

    let gradedVal = baseVal * multiplier;

    // Screen
    let screenDeduction = 0;
    const screenCond = document.getElementById('screenCondition').value;
    if (screenCond === 'Minor Scratches') screenDeduction = scratchDed;
    if (screenCond === 'Cracked / Lines') screenDeduction = crackDed;
    document.getElementById('liveScreenDed').innerText = '-₱' + screenDeduction.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Battery
    let batHealth = parseInt(document.getElementById('batteryHealth').value) || 100;
    let batDeduction = (batHealth < 80) ? batteryDed : 0;
    document.getElementById('liveBatteryDed').innerText = '-₱' + batDeduction.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Hardware
    let hardwareDeduction = 0;
    if (!document.getElementById('biometricsCheck').checked) hardwareDeduction += biometricsDed;
    if (!document.getElementById('camerasCheck').checked) hardwareDeduction += cameraDed;
    document.getElementById('liveHardwareDed').innerText = '-₱' + hardwareDeduction.toLocaleString('en-US', {minimumFractionDigits: 2});

    let finalTotal = Math.max(1000, gradedVal - (screenDeduction + batDeduction + hardwareDeduction));
    const totalElem = document.getElementById('liveTotal');
    if (typeof animateNumber === 'function' && window._lastAppraisalVal !== undefined) {
        animateNumber(totalElem, window._lastAppraisalVal, finalTotal, 300, '₱');
    } else {
        totalElem.innerText = '₱' + finalTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    }
    window._lastAppraisalVal = finalTotal;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
