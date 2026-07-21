<?php

session_start();

if (isset($_SESSION['user_id'])) { 
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit(); 
}

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

/** @var mysqli $conn */

$error = '';
$saved_email = isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email'], ENT_QUOTES, 'UTF-8') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "يرجى ملء جميع الحقول";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
           
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $user['role'];

        
                setcookie('user_email', $email, [
                    'expires'  => time() + (7 * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

            
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "كلمة المرور غير صحيحة";
            }
        } else {
            $error = "البريد الإلكتروني غير مسجل";
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = "تسجيل الدخول";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | مؤسسة القصر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .btn-dark-navy { 
            background-color: #13133d !important; 
            border-color: #13133d !important; 
            color: #ffffff !important;
        }
        .btn-dark-navy:hover { 
            background-color: #08034b !important; 
            border-color: #08034b !important; 
            color: #ffffff !important;
        }
        .text-dark-navy { 
            color: #13133d !important; 
        }
        .text-dark-navy:hover {
            color: #08034b !important;
            text-decoration: underline;
        }
    </style>
</head>
<body class="auth-body bg-light">

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <?php if (!empty($error)): ?>
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="auth-card card shadow-sm p-4 border-0 style="max-width: 420px; width: 100%;">
        <div class="text-center mb-4">
            <img src="assets/images/logo.svg" alt="القصر" height="70" onerror="this.style.display='none'">
            <h3 class="mt-3 fw-bold">تسجيل الدخول</h3>
            <p class="text-muted small">مرحباً بك في مؤسسة القصر التعليمية</p>
        </div>

        <form method="POST" action="login.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" placeholder="Baraa Naheda@gmail.com" value="<?php echo $saved_email; ?>" required>
                <div class="invalid-feedback">يرجى إدخال بريد إلكتروني صحيح</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">كلمة المرور</label>
                <div class="input-group has-validation">
                    <input type="password" name="password" id="loginPass" class="form-control" placeholder="كلمة المرور" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleLoginPass">عرض</button>
                    <div class="invalid-feedback">أدخل كلمة المرور</div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark-navy w-100 py-2 fw-bold fs-5 shadow-sm">دخول</button>
        </form>

        <p class="text-center mt-4 mb-1 text-muted small">
            ليس لديك حساب؟ <a href="register.php" class="text-dark-navy fw-bold">سجل الآن مجاناً</a>
        </p>
        <p class="text-center mb-0">
            <a href="index.php" class="text-muted small text-decoration-none">العودة للرئيسية</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { delay: 5000 }).show();
    });

    var form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) { 
            e.preventDefault(); 
            e.stopPropagation(); 
        }
        form.classList.add('was-validated');
    });

    var toggleBtn = document.getElementById('toggleLoginPass');
    var passInput = document.getElementById('loginPass');

    if (toggleBtn && passInput) {
        toggleBtn.addEventListener('click', function() {
            var isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            toggleBtn.textContent = isPassword ? 'إخفاء' : 'عرض';
        });
    }
});
</script>
</body>
</html>