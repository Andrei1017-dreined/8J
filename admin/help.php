<?php
$page_title = "Help & Guides";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff', 'technician');

$is_tech = has_role('technician');
$is_admin = has_role('admin');

/** A guide section: title, icon, who sees it, steps (HTML allowed), optional "open" link. */
$guides = [
    [
        'id' => 'sell', 'icon' => 'fa-cash-register', 'title' => 'Ring up a sale at the counter', 'roles' => ['admin', 'staff'],
        'link' => ['pos.php', 'Open New sale'],
        'steps' => [
            'Open <strong>New sale (POS)</strong>. Tap items on the left, or type a name / scan the barcode in the search box.',
            'Phones are added one at a time (each has its own IMEI). Use <strong>+ / −</strong> for accessories.',
            'Pick a discount if one applies, choose the customer (or leave <em>Walk-in Customer</em>), and the payment mode.',
            'For cash, type what the customer handed you. The change shows below. For GCash or card, enter the reference number.',
            'Press <strong>Complete sale</strong>, then print the receipt (80 mm slip or A4).',
        ],
        'tip' => 'Prices always come from Inventory. If something sold out a moment ago, the sale is stopped and nothing is charged.',
    ],
    [
        'id' => 'pickup', 'icon' => 'fa-bag-shopping', 'title' => 'Prepare and release an online pick-up order', 'roles' => ['admin', 'staff'],
        'link' => ['pickups.php', 'Open Pick-up orders'],
        'steps' => [
            'New reservations appear in <strong>Pick-up orders</strong> with a pink number in the menu.',
            'Get the listed items ready, then press <strong>Mark ready</strong>.',
            'When the customer arrives, press <strong>Release</strong> and ask for their <strong>claim code</strong> (e.g. 8J-4821). Typing just the 4 digits works.',
            'Choose how they paid and confirm. This records the sale and gives you a receipt to print.',
            'Customer changed their mind? Use <strong>Cancel</strong>. Reserved phones go straight back on sale.',
        ],
        'tip' => 'Orders that aren\'t collected within 2 days of the pick-up date expire on their own, and the phones are released.',
    ],
    [
        'id' => 'repair', 'icon' => 'fa-screwdriver-wrench', 'title' => 'Take in and finish a repair', 'roles' => ['admin', 'staff', 'technician'],
        'link' => ['repairs.php', 'Open Repair jobs'],
        'steps' => [
            '<strong>Walk-in:</strong> press <strong>New repair job</strong>, fill in the customer, phone and problem, then print the claim stub for the customer.',
            '<strong>Booked online:</strong> these show under <em>Booked online</em>. When the customer drops the phone off, open <strong>Update</strong> and set it to <em>Received</em>.',
            'As work goes on, move it through <em>Diagnosing → Waiting for parts → Repairing</em> and add notes. The customer sees each step on the tracking page.',
            'When it\'s fixed, set <strong>Ready for pick-up</strong> and enter the final bill.',
            'When the customer collects it, set <strong>Completed &amp; released</strong>. The service warranty starts then.',
        ],
        'tip' => 'Customers track a repair with either the job number or their mobile number. Use the search box to find a job the same way.',
    ],
    [
        'id' => 'buy', 'icon' => 'fa-hand-holding-dollar', 'title' => 'Buy or trade-in a customer\'s phone', 'roles' => ['admin', 'staff', 'technician'],
        'link' => ['appraisal.php', 'Open Appraise a phone'],
        'steps' => [
            '<strong>Step 1: Appraise.</strong> Choose the model, check the screen, battery, cameras and biometrics. The phone must be signed out of iCloud / Google.',
            'You get a suggested value. You may adjust it, then save.',
            '<strong>Step 2a: Trade-in / swap</strong> if they want one of our phones. Pick our phone; the difference to pay (or refund) is worked out for you.',
            '<strong>Step 2b: Buy a phone (cash)</strong> if they just want cash. A valid ID, address and the anti-fencing declaration are required by law (P.D. 1612).',
            'Print the trade or buyout papers and have the customer sign. The phone is added to Inventory automatically.',
        ],
        'tip' => 'Each appraisal can be used once. To buy the same phone again later, appraise it again.',
    ],
    [
        'id' => 'stock', 'icon' => 'fa-boxes-stacked', 'title' => 'Add and adjust stock', 'roles' => ['admin', 'staff'],
        'link' => ['inventory.php', 'Open Inventory'],
        'steps' => [
            '<strong>Add phone to stock</strong>: enter the IMEI (from the box or *#06#), condition, battery health, buy price and selling price.',
            '<strong>Add accessory</strong>: give it a category, cost and selling price, the quantity, and the reorder level (when to warn you).',
            'Delivery came in? Tap <strong>+5</strong> / <strong>+10</strong>, or <strong>Adjust…</strong> for any amount. Damaged, lost or miscounted? Use <strong>Adjust…</strong> → <em>Remove</em> and pick a reason (it\'s kept in the activity log).',
            'The pink number next to <em>Inventory</em> shows how many accessories are running low.',
        ],
        'tip' => 'Selling prices include 12% VAT. The receipt shows the VAT breakdown automatically.',
    ],
    [
        'id' => 'owner', 'icon' => 'fa-chart-line', 'title' => 'Owner tools: reports, activity log, staff', 'roles' => ['admin'],
        'link' => ['reports.php', 'Open Sales reports'],
        'steps' => [
            '<strong>Sales reports</strong>: sales, repair income and buyouts for any date range, with CSV export.',
            '<strong>Activity log</strong>: who did what and when (sales, releases, cancellations, sign-ins). Useful when something doesn\'t add up.',
            '<strong>Staff accounts</strong>: add staff, change roles, reset passwords, or deactivate someone who left.',
        ],
        'tip' => 'Give each person their own account. Shared logins make the activity log useless.',
    ],
];
$guides = array_values(array_filter($guides, fn($g) => has_role(...$g['roles'])));

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.help-step { counter-increment: step; position: relative; padding-left: 38px; margin-bottom: 10px; }
.help-step::before { content: counter(step); position: absolute; left: 0; top: 0; width: 26px; height: 26px; border-radius: 50%; background: #fce7f3; color: #be185d; font-weight: 800; font-size: .8rem; display: grid; place-items: center; }
.help-steps { counter-reset: step; list-style: none; padding: 0; margin: 0 0 12px; }
.help-tip { background: #fff8fb; border: 1px solid #fce7f3; border-radius: 12px; padding: 10px 14px; font-size: .85rem; color: #6b5563; }
.role-table td, .role-table th { font-size: .85rem; }
.accordion-button:not(.collapsed) { background: #fff5f9; color: #831843; box-shadow: none; }
.accordion-button:focus { box-shadow: 0 0 0 3px rgba(249,168,212,.35); }
</style>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="mb-4 border-bottom pb-3">
                <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-circle-question text-primary me-2"></i>Help &amp; guides</h2>
                <p class="text-muted small mb-0">Short, step-by-step guides for everyday tasks. Tap a topic to open it.</p>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="accordion" id="helpAccordion">
                        <?php foreach ($guides as $i => $g): ?>
                            <div class="accordion-item border-0 mb-2 rounded-4 overflow-hidden shadow-sm">
                                <h3 class="accordion-header">
                                    <button class="accordion-button <?= $i ? 'collapsed' : '' ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#g-<?= $g['id'] ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>">
                                        <i class="fa-solid <?= $g['icon'] ?> me-2 text-primary"></i><?= $g['title'] ?>
                                    </button>
                                </h3>
                                <div id="g-<?= $g['id'] ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-bs-parent="#helpAccordion">
                                    <div class="accordion-body">
                                        <ol class="help-steps">
                                            <?php foreach ($g['steps'] as $s): ?><li class="help-step"><?= $s ?></li><?php endforeach; ?>
                                        </ol>
                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div class="help-tip flex-grow-1"><i class="fa-solid fa-lightbulb me-1" style="color:#db2777;"></i><?= $g['tip'] ?></div>
                                            <a href="<?= BASE_URL ?>admin/<?= $g['link'][0] ?>" class="btn btn-dark btn-sm rounded-pill px-3"><?= $g['link'][1] ?> <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card card-custom p-4 mb-4">
                        <h3 class="h6 fw-bold mb-3">Good to know</h3>
                        <ul class="small text-muted ps-3 mb-0 d-grid gap-2">
                            <li><strong class="text-dark">Pink numbers</strong> in the menu mean something is waiting for you.</li>
                            <li><strong class="text-dark">"Already saved"</strong> appears if a form is sent twice (refresh or double-click). Nothing was saved twice.</li>
                            <li><strong class="text-dark">"Please try that again"</strong> appears if a page was left open too long. Reload and redo the last step.</li>
                            <li>On a shared computer, <strong class="text-dark">log out</strong> when you leave the counter (top-right menu).</li>
                            <li>On a phone, open the menu with the <strong class="text-dark">Menu</strong> button at the top.</li>
                        </ul>
                    </div>

                    <div class="card card-custom p-4">
                        <h3 class="h6 fw-bold mb-3">Who can do what</h3>
                        <div class="table-responsive">
                            <table class="table table-sm role-table mb-0">
                                <thead><tr><th></th><th class="text-center">Owner</th><th class="text-center">Staff</th><th class="text-center">Tech</th></tr></thead>
                                <tbody>
                                    <?php
                                    $matrix = [
                                        ['Sales & pick-ups', 1, 1, 0], ['Repairs', 1, 1, 1], ['Appraise phones', 1, 1, 1],
                                        ['Buy / trade-in', 1, 1, 0], ['Inventory & customers', 1, 1, 0], ['Reports, activity log, staff', 1, 0, 0],
                                    ];
                                    foreach ($matrix as [$what, $o, $s, $t]): ?>
                                        <tr>
                                            <td><?= $what ?></td>
                                            <?php foreach ([$o, $s, $t] as $ok): ?>
                                                <td class="text-center"><?= $ok ? '<i class="fa-solid fa-check text-success"></i>' : '<span class="text-muted">—</span>' ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
