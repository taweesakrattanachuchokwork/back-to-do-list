<?php
require 'vendor/autoload.php'; // โหลดไลบรารีที่ติดตั้งด้วย Composer

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// กำหนด Secret Key เดียวกับที่ใช้สร้าง Token
$secret_key = "todolist@2024_secret!API#JWT";

// ฟังก์ชันตรวจสอบ JWT Token
function verifyToken()
{
    global $secret_key;

    // รับค่า Header Authorization
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Token is required"]);
        exit();
    }

    // ดึง Token ออกมาจาก Header
    $authHeader = $headers['Authorization'];
    $token = str_replace("Bearer ", "", $authHeader);

    try {
        // ตรวจสอบและถอดรหัส Token
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Invalid token"]);
        exit();
    }
}
