<?php

$active_page = $active_page ?? ''; 

$menu = [
    'dashboard'    => ['dashboard.php',   'الرئيسية'],
    'attendance'   => ['attendance.php',  'الحضور اليومي'],
    'courses'      => ['courses.php',     'الدورات'],
    'categories'   => ['categories.php',  'التصنيفات'],
    'enrollments'  => ['enrollments.php', 'التسجيلات'],
    'users'        => ['users.php',       'المستخدمون'],
];

$user_display_name = $_SESSION['full_name'] ?? 'المسؤول';
?>

<button class="btn btn-dark d-md-none mb-3 w-100 d-flex align-items-center justify-content-between px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <span><i class="bi bi-list fs-4 me-2"></i> لوحة التحكم</span>
    <span class="badge bg-secondary"><?php echo htmlspecialchars($user_display_name); ?></span>
</button>

<div class="col-md-2 p-0 d-none d-md-block admin-sidebar-container">
    <div class="admin-sidebar">
        <div class="text-white text-center py-3 border-bottom border-secondary">
            <i class="bi bi-person-circle fs-3 d-block mb-1 text-warning"></i>
            <p class="mt-1 mb-0 fw-bold"><?php echo htmlspecialchars($user_display_name); ?></p>
            <small class="text-muted">لوحة الإدارة</small>
        </div>
        <nav class="nav flex-column mt-2">
            <?php foreach ($menu as $key => $item): ?>
                <a href="<?php echo $item[0]; ?>"
                   class="nav-link <?php echo ($active_page === $key) ? 'active fw-bold' : 'text-white-50'; ?>">
                   <?php echo $item[1]; ?>
                </a>
            <?php endforeach; ?>
            
            <hr class="border-secondary my-2">
            
            <a href="../index.php" class="nav-link text-info">
                <i class="bi bi-house me-1"></i> العودة للموقع
            </a>
            <a href="../logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right me-1"></i> تسجيل الخروج
            </a>
        </nav>
    </div>
</div>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold">لوحة التحكم</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center pb-3 border-bottom border-secondary mb-3">
            <p class="mb-0 fw-bold text-warning"><?php echo htmlspecialchars($user_display_name); ?></p>
        </div>
        <nav class="nav flex-column">
            <?php foreach ($menu as $key => $item): ?>
                <a href="<?php echo $item[0]; ?>" 
                   class="nav-link <?php echo ($active_page === $key) ? 'active text-warning fw-bold' : 'text-white'; ?>">
                    <?php echo $item[1]; ?>
                </a>
            <?php endforeach; ?>
            
            <hr class="border-secondary my-2">
            
            <a href="../index.php" class="nav-link text-info">
                <i class="bi bi-house me-1"></i> العودة للموقع
            </a>
            <a href="../logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right me-1"></i> تسجيل الخروج
            </a>
        </nav>
    </div>
</div>