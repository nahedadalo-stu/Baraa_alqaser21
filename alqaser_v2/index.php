<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */

$page_title = "الرئيسية";

$courses_result = mysqli_query($conn,
    "SELECT c.*, cat.name AS category_name
     FROM courses c
     JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'active'
     ORDER BY c.created_at DESC
     LIMIT 6"
);

$team_members = [
    [
        'name' => 'براء شحادة', 
        'role' => 'مؤسس المبادرة', 
        'bio' => 'طالب جامعي ومبادر مجتمعي، أسس القصر ليكون مساحة تعليمية آمنة تدعم طلاب غزة بعد الحرب.', 
        'icon' => 'bi-person-badge-fill', 
        'color'=> '#120657', 
        'image' => 'https://i.ibb.co/YFHFF76M/Whats-App-Image-2026-07-03-at-3-10-43-PM.jpg'
    ],
    [
        'name' => 'ناهدة الدلو', 
        'role' => 'مديرة التطوير', 
        'bio' => 'مسؤولة عن إدارة البرامج التدريبية وتطوير خدمات المساحة لطلابنا.', 
        'icon' => 'bi-person-hearts', 
        'color'=> '#0d6efd'
    ],
];

$services = [
    ['icon'=>'bi-lightning-charge-fill', 'title'=>'كهرباء مستمرة', 'desc'=>'مصدر كهرباء دائم يمكّن الطلاب من الدراسة والعمل دون انقطاع.', 'color'=>'#f39c12'],
    ['icon'=>'bi-wifi', 'title'=>'إنترنت مجاني', 'desc'=>'شبكة إنترنت مجانية وسريعة لتصفح المصادر الدراسية والتواصل الأكاديمي.', 'color'=>'#0d6efd'],
    ['icon'=>'bi-printer-fill', 'title'=>'طباعة الملفات', 'desc'=>'خدمة طباعة مجانية للملفات الجامعية والثانوية والأبحاث.', 'color'=>'#9b59b6'],
    ['icon'=>'bi-cup-hot-fill', 'title'=>'مكان هادئ للدراسة', 'desc'=>'بيئة دراسية هادئة ومريحة تساعد الطلاب على التركيز والإنتاجية.', 'color'=>'#13133d'],
];

$support_ways = [
    ['icon'=>'bi-cash-coin', 'title'=>'تبرّع', 'desc'=>'ساهم في تغطية تكاليف الكهرباء والإنترنت والطباعة لطلابنا.', 'color'=>'#13133d', 'link'=>'index.php#support'],
    ['icon'=>'bi-heart-fill', 'title'=>'تطوّع', 'desc'=>'انضم لفريقنا وقدّم وقتك ومهاراتك لدعم طلاب القصر.', 'color'=>'#e74c3c', 'link'=>'register.php'],
    ['icon'=>'bi-hand-index-thumb-fill','title'=>'انضم كشريك', 'desc'=>'تعاون معنا كمؤسسة أو جهة داعمة لتوسيع أثر المساحة.', 'color'=>'#0d6efd', 'link'=>'index.php#contact'],
    ['icon'=>'bi-megaphone-fill', 'title'=>'شارك رسالتنا', 'desc'=>'ساعدنا في نشر الوعي بمساحة القصر من خلال مشاركة صفحتنا.', 'color'=>'#f39c12', 'link'=>'index.php#support'],
];
?>

<?php include 'includes/header.php'; ?>

<section class="hero-section position-relative overflow-hidden" 
         style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=600'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="hero-overlay" style="background: linear-gradient(135deg, rgba(0,0,51,0.85), rgba(5, 5, 61, 0.8)); position: absolute; top:0; left:0; width:100%; height:100%; z-index:1;"></div>
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center min-vh-60 py-5">
            <div class="col-lg-7 text-white">
                <h1 class="display-5 fw-black mb-3 lh-sm">مؤسسة القصر<br><span class="text-warning">التعليمية</span></h1>
                <p class="lead opacity-90 mb-4">نحو تعليم مستدام وبيئة تعليمية آمنة لطلابنا في غزة —<br>كهرباء، إنترنت، طباعة، ومكان هادئ للدراسة، إضافة لدورات تدريبية دورية.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <!-- 1. أزرار الزائر (قبل تسجيل الدخول) -->
                        <a href="register.php" class="btn btn-warning btn-lg fw-bold px-4"><i class="bi bi-person-plus me-2"></i> سجل الآن مجاناً</a>
                        <a href="courses.php" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-book me-2"></i> تصفح الدورات</a>
                    <?php else: ?>
                        <!-- المستخدم مسجل دخول، نفحص الرتبة الآن -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <!-- 2. أزرار مسؤول النظام (Admin) -->
                            <a href="admin/dashboard.php" class="btn btn-warning btn-lg fw-bold px-4"><i class="bi bi-speedometer2 me-2"></i> لوحة التحكم</a>
                        <?php else: ?>
                            <!-- 3. أزرار الطلاب والأعضاء العاديين -->
                            <a href="courses.php" class="btn btn-warning btn-lg fw-bold px-4"><i class="bi bi-book me-2"></i> تصفح الدورات</a>
                            <a href="my_courses.php" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-journal-check me-2"></i> دوراتي</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="py-5" style="background:#f8fffe">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title mt-2">مساحة القصر التعليمية</h2>
                <p class="text-muted lh-lg"><strong>رسالتنا:</strong> تعزيز طلاب غزة من خلال توفير بيئة تعليمية داعمة.</p>
                <p class="text-muted lh-lg"><strong>رؤيتنا:</strong> الاستثمار في الطالب الغزي هو أسمى أشكال الإعمار.</p>
            </div>
            <div class="col-lg-6 text-center">
                <div class="about-visual position-relative" style="min-height: 300px; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/lSi4d536s9Q?autoplay=1&mute=1&loop=1&playlist=lSi4d536s9Q" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title mt-2">خدماتنا لطلاب القصر</h2></div>
        <div class="row g-4">
            <?php foreach ($services as $srv): ?>
            <div class="col-md-3 col-sm-6">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto" style="background:<?php echo $srv['color']; ?>20; color:<?php echo $srv['color']; ?>">
                        <i class="bi <?php echo $srv['icon']; ?>"></i>
                    </div>
                    <h5 class="fw-bold mt-3"><?php echo $srv['title']; ?></h5>
                    <p class="text-muted small"><?php echo $srv['desc']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background:#f8fffe">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title mt-2">الدورات المتاحة</h2></div>
        <div class="row g-4">
            <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <div class="col-md-4">
                    <div class="card course-card h-100">
                        <?php if (!empty($course['image'])): ?>
                            <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge mb-2" style="background-color: #13133d;"><?php echo htmlspecialchars($course['category_name']); ?></span>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted small"><?php echo mb_substr(htmlspecialchars($course['description'] ?? ''), 0, 90, 'UTF-8'); ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($course['duration'] ?? 'غير محدد'); ?></span>
                            <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm text-white" style="background-color: #13133d;">التفاصيل والتسجيل</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted fs-5">لا توجد دورات متاحة حالياً، يرجى التحقق لاحقاً.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="team" class="py-5" style="background:#f8fffe">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title mt-2">أعضاء المؤسسة</h2></div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($team_members as $member): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="team-card text-center">
                    <div class="team-avatar mx-auto mb-3" style="background:<?php echo $member['color']; ?>20; overflow:hidden;">
                        <?php if (isset($member['image'])): ?>
                            <img src="<?php echo $member['image']; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="bi <?php echo $member['icon']; ?>" style="color:<?php echo $member['color']; ?>;font-size:3rem"></i>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                    <span class="badge mb-2" style="background:<?php echo $member['color']; ?>"><?php echo htmlspecialchars($member['role']); ?></span>
                    <p class="text-muted small mt-2"><?php echo htmlspecialchars($member['bio']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="support" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title mt-2">كن شريكاً بالدعم</h2>
            <p class="text-muted">استمرار مساحة القصر بخدمة طلاب غزة يحتاج دعمكم بأكثر من طريقة</p>
        </div>
        <div class="row g-4 mb-5">
            <?php foreach ($support_ways as $way): ?>
            <div class="col-md-3 col-sm-6">
                <div class="support-card text-center">
                    <div class="support-icon mx-auto" style="background:<?php echo $way['color']; ?>20;color:<?php echo $way['color']; ?>">
                        <i class="bi <?php echo $way['icon']; ?>"></i>
                    </div>
                    <h5 class="fw-bold mt-3"><?php echo htmlspecialchars($way['title']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($way['desc']); ?></p>
                    <a href="<?php echo htmlspecialchars($way['link']); ?>" class="btn btn-sm mt-1 text-white" style="background:<?php echo $way['color']; ?>;border:none">
                        <?php echo htmlspecialchars($way['title']); ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="support-cta text-white text-center" style="background: #13133d; padding: 40px; border-radius: 15px;">
                    <h3 class="fw-bold mb-2">معًا نبني التعليم ونستعيد الأمل</h3>
                    <p class="mb-4 opacity-90 fs-5">كل بصمة دعم تفتح لطالب في القصر باباً نحو مستقبله</p>
                    <a href="https://chat.whatsapp.com/LIjFpFoWuYnHB0Jqq20bYa" target="_blank" class="btn btn-warning btn-lg fw-bold px-5"> 
                        انضم كشريك الآن 
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>