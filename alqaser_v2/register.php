<?php
/** @var mysqli $conn */
session_start();
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$errors = [];
$form_data = ['full_name'=>'', 'email'=>'', 'phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($_POST['full_name'] ?? '', $conn);
    $email     = clean($_POST['email']     ?? '', $conn);
    $phone     = clean($_POST['phone']     ?? '', $conn);
    $password  = $_POST['password']  ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    $form_data = ['full_name'=>$full_name, 'email'=>$email, 'phone'=>$phone];

    if (empty($full_name)) $errors[] = "الاسم الكامل مطلوب";
    elseif (mb_strlen($full_name) < 3) $errors[] = "الاسم يجب أن يكون 3 أحرف على الأقل";

    if (empty($email)) $errors[] = "البريد الإلكتروني مطلوب";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "صيغة البريد الإلكتروني غير صحيحة";
    else {
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "هذا البريد الإلكتروني مسجل مسبقاً";
        }
        mysqli_stmt_close($stmt_check);
    }

    if (strlen($password) < 6) $errors[] = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
    if ($password !== $confirm) $errors[] = "كلمة المرور وتأكيدها غير متطابقتين";

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'student')");
        mysqli_stmt_bind_param($stmt_insert, "ssss", $full_name, $email, $hashed, $phone);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            mysqli_stmt_close($stmt_insert);
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "حدث خطأ أثناء الحفظ، يرجى المحاولة لاحقاً";
        }
        mysqli_stmt_close($stmt_insert);
    }
}
$page_title = "تسجيل حساب جديد";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | مؤسسة القصر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .btn-success { background-color: #13133d !important; border-color: #13133d !important; }
        .btn-success:hover { background-color: #08034b !important; border-color: #08034b !important; }
        .text-success { color: #13133d !important; }
        .btn-outline-secondary { border-color: #13133d !important; color: #13133d !important; }
        .btn-outline-secondary:hover { background-color: #13133d !important; color: white !important; }
    </style>
</head>
<body class="auth-body">

<div class="container py-4">
    <div class="auth-card mx-auto" style="max-width: 500px;">
        <div class="text-center mb-4">
            <h3 class="mt-3 fw-bold">مؤسسة القصر التعليمية</h3>
            <p class="text-muted">إنشاء حساب جديد</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" placeholder="أدخل اسمك الكامل" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" required minlength="3">
                <div class="invalid-feedback">يرجى إدخال الاسم الكامل (3 أحرف على الأقل)</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="Baraa Naheda@gmail.com" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                <div class="invalid-feedback">يرجى إدخال بريد إلكتروني صحيح</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">رقم الهاتف <span class="text-muted small">(اختياري)</span></label>
                <input type="tel" name="phone" class="form-control" placeholder="059xxxxxxx" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <input type="password" name="password" id="pass1" class="form-control" placeholder="6 أحرف على الأقل" required minlength="6">
                    <button class="btn btn-outline-secondary" type="button" id="togglePass1">إظهار</button>
                    <div class="invalid-feedback">كلمة المرور يجب أن تكون 6 أحرف على الأقل</div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <input type="password" name="confirm_password" id="pass2" class="form-control" placeholder="أعد كتابة كلمة المرور" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePass2">إظهار</button>
                    <div class="invalid-feedback">يرجى تأكيد كلمة المرور</div>
                </div>
                <small id="matchMsg" class="mt-1 d-block"></small>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold fs-5">إنشاء الحساب</button>
        </form>

        <p class="text-center mt-3 text-muted">لديك حساب؟ <a href="login.php" class="text-success fw-bold">سجل الدخول</a></p>
        <p class="text-center mb-0"><a href="index.php" class="text-muted small">العودة للرئيسية</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePass1').addEventListener('click', function() {
    var input = document.getElementById('pass1');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'text' ? 'إخفاء' : 'إظهار';
});

document.getElementById('togglePass2').addEventListener('click', function() {
    var input = document.getElementById('pass2');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'text' ? 'إخفاء' : 'إظهار';
});

document.getElementById('pass2').addEventListener('input', function() {
    var p1 = document.getElementById('pass1').value;
    var p2 = this.value;
    var msg = document.getElementById('matchMsg');
    if (p2.length === 0) { msg.textContent = ''; return; }
    msg.textContent = (p1 === p2) ? 'كلمتا المرور متطابقتان' : 'كلمتا المرور غير متطابقتين';
    msg.className = 'mt-1 d-block ' + ((p1 === p2) ? 'text-success' : 'text-danger');
});

(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
</body>
</html>