<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
/** @var mysqli $conn */
check_admin();
$is_admin   = true;
$page_title = "إدارة التسجيلات";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['approve', 'reject'], true)) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $stmt = mysqli_prepare($conn, "UPDATE enrollments SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $msg = ($action === 'approve') ? 'تم قبول الطالب في الدورة' : 'تم رفض طلب التسجيل';
        set_message('success', $msg);
    }
    header("Location: enrollments.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = mysqli_prepare($conn, "DELETE FROM enrollments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    set_message('success', 'تم حذف التسجيل بنجاح');
    header("Location: enrollments.php");
    exit();
}


$filter = $_GET['status'] ?? '';
$where  = '';

if (in_array($filter, ['pending', 'approved', 'rejected'], true)) {
    $where = "WHERE e.status = ?";
}

if ($where !== '') {
    $stmt_list = mysqli_prepare($conn, "
        SELECT e.*, u.full_name, u.email, u.phone, c.title AS course_title
        FROM enrollments e
        JOIN users u   ON e.user_id   = u.id
        JOIN courses c ON e.course_id = c.id
        $where
        ORDER BY e.enrolled_at DESC
    ");
    mysqli_stmt_bind_param($stmt_list, "s", $filter);
    mysqli_stmt_execute($stmt_list);
    $enrollments = mysqli_stmt_get_result($stmt_list);
} else {
    $enrollments = mysqli_query($conn, "
        SELECT e.*, u.full_name, u.email, u.phone, c.title AS course_title
        FROM enrollments e
        JOIN users u   ON e.user_id   = u.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.enrolled_at DESC
    ");
}
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php $active_page = 'enrollments'; include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <h4 class="fw-bold mb-4">إدارة التسجيلات</h4>

            <div class="mb-3 d-flex gap-2">
                <a href="enrollments.php"
                   class="btn btn-sm <?php echo $filter === '' ? 'btn-dark' : 'btn-outline-dark'; ?>">الكل</a>
                <a href="enrollments.php?status=pending"
                   class="btn btn-sm <?php echo $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">قيد المراجعة</a>
                <a href="enrollments.php?status=approved"
                   class="btn btn-sm <?php echo $filter === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">مقبول</a>
                <a href="enrollments.php?status=rejected"
                   class="btn btn-sm <?php echo $filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">مرفوض</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>الإيميل</th>
                                    <th>الدورة</th>
                                    <th>الحالة</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if ($enrollments && mysqli_num_rows($enrollments) > 0): ?>
                                <?php $i = 1; while ($row = mysqli_fetch_assoc($enrollments)): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                        <td><?php echo enrollment_status_badge($row['status']); ?></td>
                                        <td><?php echo date('Y/m/d', strtotime($row['enrolled_at'])); ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <a href="enrollments.php?action=approve&id=<?php echo $row['id']; ?>"
                                                   class="btn btn-sm btn-success me-1"
                                                   onclick="return confirm('هل تريد قبول هذا الطالب في الدورة؟')">
                                                    قبول
                                                </a>
                                                <a href="enrollments.php?action=reject&id=<?php echo $row['id']; ?>"
                                                   class="btn btn-sm btn-danger me-1"
                                                   onclick="return confirm('هل تريد رفض هذا الطلب؟')">
                                                    رفض
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- أضيف تأكيد عند إتاحة زر الحذف -->
                                            <a href="enrollments.php?delete=<?php echo $row['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('هل أنت تأكد من رغبتك في حذف هذا التسجيل؟')">
                                                حذف
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">لا توجد تسجيلات لعرضها.</td>
                                </tr>
                            <?php endif; ?>
                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>