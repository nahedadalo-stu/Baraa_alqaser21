<?php

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
/** @var mysqli $conn */
check_admin();

$is_admin   = true;
$page_title = "لوحة التحكم";

$recent_enrollments = mysqli_query($conn, "
    SELECT e.*, u.full_name, c.title AS course_title, e.status
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
    
        <?php $active_page = 'dashboard'; include '../includes/admin_sidebar.php'; ?>


        <div class="col-12 col-md-10 p-4">
            <h4 class="fw-bold mb-4">لوحة التحكم</h4>

            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    آخر التسجيلات
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>الدورة</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_enrollments) > 0): ?>
                                    <?php $i = 1; while ($row = mysqli_fetch_assoc($recent_enrollments)): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                            <td><?php echo enrollment_status_badge($row['status']); ?></td>
                                            <td><?php echo date('Y/m/d', strtotime($row['enrolled_at'])); ?></td>
                                            <td>
                                                <a href="enrollments.php" class="btn btn-sm btn-outline-success">إدارة</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد تسجيلات بعد</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
