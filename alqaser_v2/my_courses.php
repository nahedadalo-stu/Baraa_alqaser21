<?php

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */
check_login();

$page_title = "دوراتي";
$uid = $_SESSION['user_id'];

$sql = "SELECT e.id AS enrollment_id, e.status, e.enrolled_at,
               c.title, c.duration, c.image,
               cat.name AS category_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN categories cat ON c.category_id = cat.id
        WHERE e.user_id = $uid
        ORDER BY e.enrolled_at DESC";

$result = mysqli_query($conn, $sql);
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">
        دوراتي المسجلة
    </h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge" style="background-color: #13133d;">
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                </span>
                                <!-- شارة الحالة -->
                                <?php echo enrollment_status_badge($row['status']); ?>
                            </div>

                            <h5 class="fw-bold"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="text-muted small mb-1">
                                المدة: <?php echo htmlspecialchars($row['duration'] ?? '-'); ?>
                            </p>
                            <p class="text-muted small">
                                تاريخ التسجيل: <?php echo date('Y/m/d', strtotime($row['enrolled_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <p class="text-muted mt-3 fs-5">لم تسجل في أي دورة بعد</p>
            <a href="courses.php" class="btn text-white mt-2" style="background-color: #13133d;">
                تصفح الدورات
            </a>
        </div>
    <?php endif; ?>
</div>