<?php
$host = "localhost";  // หรือ IP ของเซิร์ฟเวอร์ฐานข้อมูล
$db_name = "To_Do_list";  // ชื่อฐานข้อมูล
$username = "root";  // ชื่อผู้ใช้ฐานข้อมูล
$password = "";  // รหัสผ่านฐานข้อมูล (ถ้าใช้ XAMPP ปกติจะเป็นค่าว่าง)

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
