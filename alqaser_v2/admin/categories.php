<?php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';



check_admin();
$is_admin   = true;
$page_title = "إدارة التصنيفات";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        $name = clean($_POST['name'] ?? '', $conn);
        $desc = clean($_POST['description'] ?? '', $conn);

        if (!empty($name)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, description) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $name, $desc);
            
            if (mysqli_stmt_execute($stmt)) {
                if (function_exists('set_message')) set_message('success', 'تم إضافة التصنيف بنجاح');
            } else {
                if (function_exists('set_message')) set_message('danger', 'حدث خطأ أثناء إضافة التصنيف');
            }
            mysqli_stmt_close($stmt);
        }
    }

    elseif ($_POST['action'] === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = clean($_POST['name'] ?? '', $conn);
        $desc = clean($_POST['description'] ?? '', $conn);

        if (!empty($name) && $id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE categories SET name=?, description=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssi", $name, $desc, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                if (function_exists('set_message')) set_message('success', 'تم تحديث التصنيف بنجاح');
            } else {
                if (function_exists('set_message')) set_message('danger', 'حدث خطأ أثناء تحديث التصنيف');
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    header("Location: categories.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt_check = mysqli_prepare($conn, "SELECT COUNT(*) FROM courses WHERE category_id = ?");
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_bind_result($stmt_check, $courses_count);
    mysqli_stmt_fetch($stmt_check);
    mysqli_stmt_close($stmt_check);

    if ($courses_count > 0) {
        if (function_exists('set_message')) {
            set_message('danger', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على دورات مسجلة!');
        }
    } else {
        $stmt_del = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $id);
        
        if (mysqli_stmt_execute($stmt_del)) {
            if (function_exists('set_message')) set_message('success', 'تم حذف التصنيف بنجاح');
        } else {
            if (function_exists('set_message')) set_message('danger', 'تعذر حذف التصنيف');
        }
        mysqli_stmt_close($stmt_del);
    }

    header("Location: categories.php");
    exit();
}

$categories = mysqli_query($conn, "
    SELECT cat.*, COUNT(c.id) AS courses_count
    FROM categories cat
    LEFT JOIN courses c ON cat.id = c.category_id
    GROUP BY cat.id
    ORDER BY cat.name ASC
");
?>
<?php include '../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="row">
        <?php $active_page = 'categories'; include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">إدارة التصنيفات</h4>
                <button class="btn text-white fw-bold" data-bs-toggle="modal" data-bs-target="#addModal" style="background-color: #13133d;">
                    <i class="bi bi-plus-lg me-1"></i> إضافة تصنيف
                </button>
            </div>

            <?php if (function_exists('display_message')) display_message(); ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم التصنيف</th>
                                    <th>الوصف</th>
                                    <th>عدد الدورات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categories && mysqli_num_rows($categories) > 0): ?>
                                    <?php $i = 1; while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($cat['description'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge bg-primary px-2 py-1"><?php echo (int)$cat['courses_count']; ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal"
                                                        data-id="<?php echo $cat['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-desc="<?php echo htmlspecialchars($cat['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    تعديل
                                                </button>
                                                
                                                <a href="javascript:void(0);" 
                                                   onclick="confirmDelete(<?php echo $cat['id']; ?>, <?php echo (int)$cat['courses_count']; ?>)"
                                                   class="btn btn-sm btn-outline-danger">
                                                    حذف
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد تصنيفات مسجلة حالياً</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #13133d;">
                <h5 class="modal-title fw-bold">إضافة تصنيف جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: تقنية المعلومات">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر للتصنيف (اختياري)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn text-white fw-bold" style="background-color: #13133d;">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">تعديل التصنيف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var editModal = document.getElementById('editModal');
if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        document.getElementById('edit_id').value   = btn.getAttribute('data-id');
        document.getElementById('edit_name').value = btn.getAttribute('data-name');
        document.getElementById('edit_desc').value = btn.getAttribute('data-desc');
    });
}

function confirmDelete(id, coursesCount) {
    if (coursesCount > 0) {
        Swal.fire({
            title: 'لا يمكن الحذف!',
            text: 'هذا التصنيف يحتوي على ' + coursesCount + ' دورة مرتبطة به. يرجى نقل أو حذف الدورات أولاً.',
            icon: 'error',
            confirmButtonColor: '#13133d',
            confirmButtonText: 'حسناً'
        });
        return;
    }

    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: "لن تتمكن من استعادة هذا التصنيف بعد الحذف!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#13133d',
        cancelButtonColor: '#d33',
        confirmButtonText: 'نعم، احذفه!',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'categories.php?delete=' + id;
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
