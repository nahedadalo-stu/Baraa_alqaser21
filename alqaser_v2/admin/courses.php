<?php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

/** @var mysqli $conn */

check_admin();
$is_admin   = true;
$page_title = "إدارة الدورات";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $title       = clean($_POST['title']       ?? '', $conn);
    $description = clean($_POST['description'] ?? '', $conn);
    $duration    = clean($_POST['duration']    ?? '', $conn);
    $seats       = (int)($_POST['seats'] ?? 30);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status      = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';

    if (empty($title) || $category_id === 0) {
        set_message('danger', 'العنوان والتصنيف مطلوبان');
    } else {

        if ($action === 'add') {
            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $uploaded = upload_image($_FILES['image'], 'courses');
                if ($uploaded) {
                    $image = $uploaded;
                } else {
                    set_message('warning', 'نوع الصورة غير مدعوم، تم الحفظ بدون صورة');
                }
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO courses (category_id, title, description, duration, seats, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssiss", $category_id, $title, $description, $duration, $seats, $image, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                set_message('success', 'تم إضافة الدورة بنجاح');
            } else {
                set_message('danger', 'حدث خطأ أثناء إضافة الدورة');
            }
            mysqli_stmt_close($stmt);
        }

        elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);

            $stmt_old = mysqli_prepare($conn, "SELECT image FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($stmt_old, "i", $id);
            mysqli_stmt_execute($stmt_old);
            $res_old = mysqli_stmt_get_result($stmt_old);
            $old = mysqli_fetch_assoc($res_old);
            mysqli_stmt_close($stmt_old);

            $image = $old['image'] ?? '';

            if (!empty($_FILES['image']['name'])) {
                $uploaded = upload_image($_FILES['image'], 'courses');
                if ($uploaded) {
                    if ($image && file_exists("../uploads/courses/$image")) {
                        unlink("../uploads/courses/$image");
                    }
                    $image = $uploaded;
                }
            }

            $stmt = mysqli_prepare($conn, "UPDATE courses SET category_id=?, title=?, description=?, duration=?, seats=?, image=?, status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "isssissi", $category_id, $title, $description, $duration, $seats, $image, $status, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                set_message('success', 'تم تعديل الدورة بنجاح');
            } else {
                set_message('danger', 'حدث خطأ أثناء تعديل الدورة');
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: courses.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt_old = mysqli_prepare($conn, "SELECT image FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $res_old = mysqli_stmt_get_result($stmt_old);
    $old = mysqli_fetch_assoc($res_old);
    mysqli_stmt_close($stmt_old);

    if ($old && $old['image'] && file_exists("../uploads/courses/" . $old['image'])) {
        unlink("../uploads/courses/" . $old['image']);
    }

    $stmt_del = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id);
    
    if (mysqli_stmt_execute($stmt_del)) {
        set_message('success', 'تم حذف الدورة بنجاح');
    } else {
        set_message('danger', 'تعذر حذف الدورة');
    }
    mysqli_stmt_close($stmt_del);

    header("Location: courses.php");
    exit();
}


$courses = mysqli_query($conn, "
    SELECT c.*, cat.name AS cat_name
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.created_at DESC
");


$categories_res = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
$cats_arr = [];
if ($categories_res) {
    while ($c = mysqli_fetch_assoc($categories_res)) {
        $cats_arr[] = $c;
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php $active_page = 'courses'; include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">إدارة الدورات</h4>
                <button class="btn text-white fw-bold" data-bs-toggle="modal" data-bs-target="#addModal" style="background-color: #13133d;">
                    <i class="bi bi-plus-lg me-1"></i> إضافة دورة
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
                                    <th>الصورة</th>
                                    <th>العنوان</th>
                                    <th>التصنيف</th>
                                    <th>المدة</th>
                                    <th>المقاعد</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($c = mysqli_fetch_assoc($courses)): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <?php if ($c['image']): ?>
                                            <img src="../uploads/courses/<?php echo htmlspecialchars($c['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 style="width:60px;height:45px;object-fit:cover;border-radius:6px">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center"
                                                 style="width:60px;height:45px;font-size:12px">
                                                بلا صورة
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['cat_name'] ?? 'بدون تصنيف', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['duration'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)$c['seats']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $c['status']==='active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $c['status']==='active' ? 'نشطة' : 'موقوفة'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning me-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?php echo $c['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-desc="<?php echo htmlspecialchars($c['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-duration="<?php echo htmlspecialchars($c['duration'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-seats="<?php echo (int)$c['seats']; ?>"
                                                data-cat="<?php echo (int)$c['category_id']; ?>"
                                                data-status="<?php echo htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                            تعديل
                                        </button>
                                        <a href="courses.php?delete=<?php echo $c['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('هل أنتِ متأكدة من حذف هذه الدورة؟');">
                                            حذف
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($i === 1): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">لا توجد دورات مسجلة حالياً</td></tr>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #13133d;">
                <h5 class="modal-title fw-bold">إضافة دورة جديدة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="courses.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">عنوان الدورة *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">التصنيف *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($cats_arr as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">المدة</label>
                            <input type="text" name="duration" class="form-control" placeholder="مثال: 3 أسابيع">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">عدد المقاعد</label>
                            <input type="number" name="seats" class="form-control" value="30" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="active">نشطة</option>
                                <option value="inactive">موقوفة</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">صورة الدورة</label>
                            <input type="file" name="image" id="course_image" class="form-control" accept="image/*">
                            <img id="image_preview" src="" style="display:none; max-height:150px; margin-top:10px; border-radius:8px">
                        </div>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">تعديل الدورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="courses.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">عنوان الدورة *</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">التصنيف *</label>
                            <select name="category_id" id="edit_cat" class="form-select" required>
                                <?php foreach ($cats_arr as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">المدة</label>
                            <input type="text" name="duration" id="edit_duration" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">عدد المقاعد</label>
                            <input type="number" name="seats" id="edit_seats" class="form-control" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">نشطة</option>
                                <option value="inactive">موقوفة</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">صورة جديدة (اتركه فارغاً للإبقاء على الصورة الحالية)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
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
        document.getElementById('edit_id').value       = btn.getAttribute('data-id');
        document.getElementById('edit_title').value    = btn.getAttribute('data-title');
        document.getElementById('edit_desc').value     = btn.getAttribute('data-desc');
        document.getElementById('edit_duration').value = btn.getAttribute('data-duration');
        document.getElementById('edit_seats').value    = btn.getAttribute('data-seats');
        document.getElementById('edit_status').value   = btn.getAttribute('data-status');
        document.getElementById('edit_cat').value      = btn.getAttribute('data-cat');
    });
}

document.getElementById('course_image')?.addEventListener('change', function(e) {
    var preview = document.getElementById('image_preview');
    var file = e.target.files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
        preview.onload = function() {
            URL.revokeObjectURL(preview.src);
        }
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
