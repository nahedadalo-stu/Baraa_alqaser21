<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT c.*, cat.name AS category_name
                               FROM courses c
                               JOIN categories cat ON c.category_id = cat.id
                               WHERE c.id = ? AND c.status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: courses.php");
    exit();
}
$course = mysqli_fetch_assoc($result);

$already_enrolled = false;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    

    $check_stmt = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($check_stmt, "ii", $uid, $course_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $already_enrolled = (mysqli_num_rows($check_result) > 0);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    // التأكد من تسجيل الدخول
    if (!isset($_SESSION['user_id'])) {
        if (function_exists('set_message')) {
            set_message('warning', 'يجب تسجيل الدخول أولاً للتسجيل في الدورة');
        }
        header("Location: login.php");
        exit();
    }

    $uid = (int)$_SESSION['user_id'];

    if (!$already_enrolled) {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO enrollments (user_id, course_id, status) VALUES (?, ?, 'pending')");
        mysqli_stmt_bind_param($insert_stmt, "ii", $uid, $course_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            if (function_exists('set_message')) {
                set_message('success', 'تم تقديم طلب التسجيل! سيتم مراجعته من قبل الإدارة.');
            }
        } else {
            if (function_exists('set_message')) {
                set_message('danger', 'حدث خطأ، يرجى المحاولة لاحقاً');
            }
        }
    }
    header("Location: course_detail.php?id=$course_id");
    exit();
}

$page_title = $course['title'];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row g-4">
    
        <div class="col-md-8">
            <a href="courses.php" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-right me-1"></i> العودة للدورات
            </a>

            <?php if (!empty($course['image'])): ?>
                <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>"
                     class="img-fluid rounded mb-4 w-100" style="max-height:400px; object-fit:cover"
                     alt="<?php echo htmlspecialchars($course['title']); ?>">
            <?php endif; ?>

            <span class="badge mb-2 fs-6" style="background-color: #13133d; color: #ffffff;">
                <?php echo htmlspecialchars($course['category_name']); ?>
            </span>
            <h2 class="fw-bold"><?php echo htmlspecialchars($course['title']); ?></h2>

            <hr>
            <h5 class="fw-bold">وصف الدورة</h5>
            <p class="text-muted">
                <?php echo nl2br(htmlspecialchars($course['description'] ?? 'لا يوجد وصف متاح')); ?>
            </p>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm position-sticky" style="top:80px">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">معلومات الدورة</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">المدة:</td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($course['duration'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">المقاعد:</td>
                            <td class="fw-semibold"><?php echo (int)$course['seats']; ?> مقعد</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الحالة:</td>
                            <td>
                                <span class="badge" style="background-color: #13133d; color: #fff;">متاحة</span>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <?php if ($already_enrolled): ?>
                        <div class="alert alert-info text-center mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            أنت مسجل في هذه الدورة
                        </div>
                    <?php elseif (isset($_SESSION['user_id'])): ?>
                        <!-- نموذج التسجيل -->
                        <form method="POST">
                            <button type="submit" name="enroll" class="btn text-white w-100 py-2 fw-bold" style="background-color: #13133d;">
                                <i class="bi bi-person-plus me-2"></i>
                                التسجيل في الدورة
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-warning w-100 py-2 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            سجل الدخول للتسجيل
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>