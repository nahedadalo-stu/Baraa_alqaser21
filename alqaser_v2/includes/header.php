<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>مؤسسة القصر التعليمية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo isset($is_admin) ? '../' : ''; ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color: #13133d;">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?php echo isset($is_admin) ? '../' : ''; ?>index.php">
            <img src="<?php echo isset($is_admin) ? '../' : ''; ?>assets/images/logo.svg"
                 alt="شعار القصر"
                 height="42"
                 class="logo-img"
                 onerror="this.style.display='none'">
            <span class="text-white">مؤسسة القصر</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
               <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>index.php">الرئيسية</a></li>
               <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>courses.php">الدورات</a></li>
               <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>index.php#services">خدماتنا</a></li>
               <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>index.php#team">الفريق</a></li>
               <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>index.php#support">كن شريكاً</a></li>
            </ul>

          
            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '' : 'admin/'; ?>dashboard.php">لوحة التحكم</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>my_courses.php">دوراتي</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>my_attendance.php">حضوري</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <span class="nav-link text-warning fw-semibold px-2">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>logout.php">خروج</a></li>
                
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($is_admin) ? '../' : ''; ?>login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>دخول
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-warning btn-sm fw-bold px-3" href="<?php echo isset($is_admin) ? '../' : ''; ?>register.php">سجل الآن</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>