<?php

define('DB_HOST', 'localhost');   
define('DB_USER', 'root');        
define('DB_PASS', '');            
define('DB_NAME', 'alqaser_db'); 
define('DB_PORT', 3307);          
                                
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    die("خطأ في الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
