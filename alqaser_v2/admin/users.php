<?php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
/** @var mysqli $conn */
check_admin();
$is_admin   = true;
$page_title = "إدارة المستخدمين";

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
   
    if ($id == $_SESSION['user_id']) {
        set_message('danger', 'لا يمكنك حذف حسابك الخاص');
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='student'");
        set_message('success', 'تم حذف المستخدم');
    }
    header("Location: users.php");
    exit();
}


$users = mysqli_query($conn, "
    SELECT u.*, COUNT(e.id) AS courses_count
    FROM users u
    LEFT JOIN enrollments e ON e.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php $active_page = 'users'; include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <h4 class="fw-bold mb-4">المستخدمون</h4>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>الدور</th>
                                    <th>الدورات</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>حذف</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($u = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $u['role']==='admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $u['role']==='admin' ? 'مسؤول' : 'طالب'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $u['courses_count']; ?></span>
                                    </td>
                                    <td><?php echo date('Y/m/d', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <a href="users.php?delete=<?php echo $u['id']; ?>"
                                               class="btn btn-sm btn-outline-danger btn-delete">
                                                حذف
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>