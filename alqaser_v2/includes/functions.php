<?php

function clean(?string $input, $conn = null): string {
    if (is_null($input)) return '';
    
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    if ($conn instanceof mysqli) {
        return mysqli_real_escape_string($conn, $input);
    }
    
    return $input;
}

function check_login(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

function check_admin(): void {
    check_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function set_message(string $type, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['message'] = [
        'type' => $type, 
        'text' => $msg
    ];
}

function display_message(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['message'])) {
        $msg = $_SESSION['message'];
        echo '<div class="alert alert-' . htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8') . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8') .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['message']);
    }
}

function enrollment_status_badge(string $status): string {
    $map = [
        'pending'  => ['warning', 'قيد المراجعة'],
        'approved' => ['success', 'مقبول'],
        'rejected' => ['danger',  'مرفوض'],
    ];
    $s = $map[$status] ?? ['secondary', $status];
    return '<span class="badge bg-' . $s[0] . '">' . $s[1] . '</span>';
}

function attendance_status_badge(string $status): string {
    $map = [
        'present' => ['success',  'حاضر'],
        'absent'  => ['danger',   'غائب'],
        'late'    => ['warning',  'متأخر'],
    ];
    $s = $map[$status] ?? ['secondary', $status];
    return '<span class="badge bg-' . $s[0] . '">' . $s[1] . '</span>';
}

function upload_image(array $file, string $folder) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return false;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;

    $target_dir = __DIR__ . "/../uploads/{$folder}/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $new_name    = uniqid('img_', true) . '.' . $ext;
    $upload_path = $target_dir . $new_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }
    return false;
}