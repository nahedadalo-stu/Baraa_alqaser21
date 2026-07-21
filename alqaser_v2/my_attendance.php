<?php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */

check_login();

$page_title = "سجل حضوري";
$uid = $_SESSION['user_id'];

$records = mysqli_query($conn, "
    SELECT attendance_date, status, notes
    FROM attendance
    WHERE user_id = $uid
    ORDER BY attendance_date DESC
    LIMIT 60
");
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-1" style="color: #13133d;">
        سجل حضوري
    </h2>
    <p class="text-muted mb-4">تابع أيام حضورك في مساحة القصر التعليمية</p>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #13133d; color: white;">
                        <tr>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($records) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($records)): ?>
                            <tr>
                                <td><?php echo date('Y/m/d', strtotime($r['attendance_date'])); ?></td>
                                <td><?php echo attendance_status_badge($r['status']); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($r['notes'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">لم يتم تسجيل حضورك بعد</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>