<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */
$page_title = "الدورات التدريبية";

$cat_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

$where = "WHERE c.status = 'active'";
if ($cat_filter > 0) {
    $where .= " AND c.category_id = $cat_filter";
}

$sql = "SELECT c.*, cat.name AS category_name
        FROM courses c
        JOIN categories cat ON c.category_id = cat.id
        $where
        ORDER BY c.created_at DESC";

$courses_result = mysqli_query($conn, $sql);
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">الدورات التدريبية</h2>

    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="courses.php"
           class="btn <?php echo $cat_filter === 0 ? 'text-white' : 'text-dark border-dark'; ?>"
           style="<?php echo $cat_filter === 0 ? 'background-color:#000033;' : ''; ?>">
            الكل
        </a>
        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="courses.php?cat=<?php echo $cat['id']; ?>"
               class="btn <?php echo $cat_filter == $cat['id'] ? 'text-white' : 'text-dark border-dark'; ?>"
               style="<?php echo $cat_filter == $cat['id'] ? 'background-color:#000033;' : ''; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endwhile; ?>
    </div>

   
    <div class="row g-4">
        <?php if (mysqli_num_rows($courses_result) > 0): ?>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <div class="col-md-4">
                    <div class="card course-card">
                        <?php if ($course['image']): ?>
                            <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                 class="card-img-top" alt="">
                        <?php else: ?>
                            <div class="text-white d-flex align-items-center justify-content-center"
                                 style="height:200px; border-radius:12px 12px 0 0; background-color:#000033;">
                                <span>صورة الدورة</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge mb-2 text-white" style="background-color:#000033;">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </span>
                            <h5 class="card-title fw-bold">
                                <?php echo htmlspecialchars($course['title']); ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <?php echo mb_substr($course['description'] ?? '', 0, 100, 'UTF-8'); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    المدة: <?php echo htmlspecialchars($course['duration'] ?? ''); ?>
                                </small>
                                <small class="text-muted">
                                    <?php echo $course['seats']; ?> مقعد
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="course_detail.php?id=<?php echo $course['id']; ?>"
                               class="btn w-100 text-white" style="background-color:#000033;">
                                التفاصيل والتسجيل
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted mt-2">لا توجد دورات في هذا التصنيف</p>
            </div>
        <?php endif; ?>
    </div>
</div>