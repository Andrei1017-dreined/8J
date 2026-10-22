<?php
$page_title = "Categories";
require_once __DIR__ . '/../config/db.php';
require_role('admin', 'staff');

$message = '';
$types = ['accessory', 'unit', 'part'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $message = "Error: Security token expired. Please reload and try again.";
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save_category') {
                $id   = (int)($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $type = in_array($_POST['type'] ?? '', $types, true) ? $_POST['type'] : 'accessory';
                $desc = sanitize($_POST['description'] ?? '');
                if ($name === '') throw new Exception("Category name is required.");

                if ($id > 0) {
                    $pdo->prepare("UPDATE categories SET name = ?, type = ?, description = ? WHERE id = ?")->execute([$name, $type, $desc, $id]);
                    audit_log('Updated Category', 'Categories', "Category #{$id} renamed to '{$name}' ({$type})");
                    $message = "Category updated.";
                } else {
                    $pdo->prepare("INSERT INTO categories (name, type, description) VALUES (?, ?, ?)")->execute([$name, $type, $desc]);
                    audit_log('Created Category', 'Categories', "Created '{$name}' ({$type})");
                    $message = "Category created.";
                }
            } elseif ($action === 'delete_category') {
                if (!has_role('admin')) throw new Exception("Only administrators can delete categories.");
                $id = (int)($_POST['category_id'] ?? 0);
                $in_use = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $in_use->execute([$id]);
                if ((int)$in_use->fetchColumn() > 0) {
                    throw new Exception("This category still has products. Reassign or archive them first.");
                }
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
                audit_log('Deleted Category', 'Categories', "Deleted category #{$id}");
                $message = "Category deleted.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY c.type, c.name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-tags text-primary me-2"></i>Product Categories</h2>
                    <p class="text-muted small mb-0">Organize accessories, phone units and spare parts</p>
                </div>
                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#catModal" onclick="openCat(null)">
                    <i class="fa-solid fa-plus me-1"></i> New Category
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Error') === 0 ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Type</th><th>Description</th><th class="text-center">Products</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$categories): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No categories yet.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($c['name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= ucfirst($c['type']) ?></span></td>
                                        <td class="text-muted"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                                        <td class="text-center"><?= (int)$c['product_count'] ?></td>
                                        <td class="text-end text-nowrap">
                                            <button class="btn btn-outline-primary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#catModal"
                                                onclick='openCat(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)'><i class="fa-solid fa-pen"></i></button>
                                            <?php if (has_role('admin')): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>">
                                                    <button class="btn btn-outline-danger btn-sm py-0 px-2"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="category_id" id="cat_id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="cat_title">New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Name</label>
                        <input type="text" name="name" id="cat_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Type</label>
                        <select name="type" id="cat_type" class="form-select">
                            <?php foreach ($types as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" id="cat_desc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCat(c) {
    document.getElementById('cat_title').textContent = c ? 'Edit Category' : 'New Category';
    document.getElementById('cat_id').value = c ? c.id : 0;
    document.getElementById('cat_name').value = c ? c.name : '';
    document.getElementById('cat_type').value = c ? c.type : 'accessory';
    document.getElementById('cat_desc').value = c ? (c.description || '') : '';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
