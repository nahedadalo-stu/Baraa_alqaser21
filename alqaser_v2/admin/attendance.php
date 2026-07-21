<?php

session_start();

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

/** @var mysqli $conn */

check_admin();
$is_admin   = true;
$page_title = "الحضور اليومي";

$selected_date = isset($_GET['date']) ? clean($_GET['date']) : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $att_date = clean($_POST['attendance_date'] ?? '');
    $admin_id = (int)$_SESSION['user_id'];

    if (isset($_POST['status']) && is_array($_POST['status'])) {
        $stmt_save = mysqli_prepare($conn, "
            INSERT INTO attendance (user_id, attendance_date, status, recorded_by, notes)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), recorded_by = VALUES(recorded_by)
        ");

        foreach ($_POST['status'] as $uid => $status) {
            $uid    = (int)$uid;
            $status = in_array($status, ['present', 'absent', 'late']) ? $status : 'present';
            $note   = isset($_POST['notes'][$uid]) ? clean($_POST['notes'][$uid]) : '';

            mysqli_stmt_bind_param($stmt_save, "issis", $uid, $att_date, $status, $admin_id, $note);
            mysqli_stmt_execute($stmt_save);
        }
        mysqli_stmt_close($stmt_save);
    }
    set_message('success', 'تم حفظ سجل الحضور لتاريخ ' . htmlspecialchars($att_date) . ' بنجاح');
    header("Location: attendance.php?date=" . urlencode($att_date));
    exit();
}

$stmt_get = mysqli_prepare($conn, "
    SELECT u.id, u.full_name, u.email, u.phone,
           a.status AS att_status, a.notes AS att_notes
    FROM users u
    LEFT JOIN attendance a
           ON a.user_id = u.id AND a.attendance_date = ?
    WHERE u.role = 'student'
    ORDER BY u.full_name ASC
");
mysqli_stmt_bind_param($stmt_get, "s", $selected_date);
mysqli_stmt_execute($stmt_get);
$students = mysqli_stmt_get_result($stmt_get);

if (!$students) {
    die("خطأ في استعلام قاعدة البيانات: " . mysqli_error($conn));
}
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php $active_page = 'attendance'; include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h4 class="fw-bold mb-0">الحضور اليومي</h4>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <label class="fw-semibold small mb-0">التاريخ:</label>
                    <input type="date" name="date" class="form-control form-control-sm"
                           value="<?php echo htmlspecialchars($selected_date); ?>" onchange="this.form.submit()">
                </form>
            </div>

            <?php if (function_exists('display_message')) display_message(); ?>

            <form method="POST">
                <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الطالب</th>
                                        <th>الإيميل</th>
                                        <th>الحالة</th>
                                        <th>ملاحظة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; while ($st = mysqli_fetch_assoc($students)): ?>
                                    <?php $current = $st['att_status'] ?? 'present'; ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($st['full_name']); ?></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars($st['email']); ?></td>
                                        <td>
                                            <select name="status[<?php echo $st['id']; ?>]" class="form-select form-select-sm">
                                                <option value="present" <?php echo $current==='present'?'selected':''; ?>>حاضر</option>
                                                <option value="late"    <?php echo $current==='late'?'selected':''; ?>>متأخر</option>
                                                <option value="absent"  <?php echo $current==='absent'?'selected':''; ?>>غائب</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[<?php echo $st['id']; ?>]"
                                                   class="form-control form-control-sm"
                                                   value="<?php echo htmlspecialchars($st['att_notes'] ?? ''); ?>">
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($i === 1): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد طلاب مسجّلون بعد</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn text-white px-5 fw-bold" style="background-color: #13133d;">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>