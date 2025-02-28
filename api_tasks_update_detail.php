<?php
require 'middleware.php'; // เรียกใช้งาน Middleware สำหรับตรวจสอบ Token
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken(); // ฟังก์ชั่นนี้จะตรวจสอบความถูกต้องของ Token เพื่อยืนยันตัวตนผู้ใช้

header('Content-Type: application/json'); // กำหนดประเภทของข้อมูลที่จะส่งกลับเป็น JSON

$method = $_SERVER['REQUEST_METHOD']; // รับค่าของ HTTP method (GET, POST, PUT, DELETE) ที่ใช้ในการร้องขอ

// ใช้ switch เพื่อเลือกทำงานตาม HTTP method
switch ($method) {
    case 'PUT':
        updateDetail($conn); // หากเป็น PUT method ให้เรียกฟังก์ชั่น updateDetail
        break;
    default:
        http_response_code(405); // ถ้าไม่ใช่ method ที่รองรับ ให้ส่ง HTTP 405 (Method Not Allowed)
        echo json_encode(["message" => "Method not allowed"]); // ส่งข้อความ error กลับ
}

// ฟังก์ชั่นสำหรับอัปเดตข้อมูลคอมเมนต์
function updateDetail($conn)
{
    // รับข้อมูลจาก request body ที่ส่งมา
    $data = json_decode(file_get_contents('php://input'), true); // แปลงข้อมูล JSON จาก request body เป็น array

    // ตรวจสอบว่า comment_id มีข้อมูลหรือไม่
    if (!isset($data['comment_id']) || empty($data['comment_id'])) {
        http_response_code(400); // ถ้าไม่มี comment_id ส่งกลับ HTTP 400 (Bad Request)
        echo json_encode(['status' => '400', 'error' => 'comment_id is required']); // ส่งข้อความ error กลับ
        exit; // หยุดการทำงานของฟังก์ชั่น
    }

    // ตรวจสอบว่า comment มีข้อมูลหรือไม่
    if (!isset($data['comment']) || empty($data['comment'])) {
        http_response_code(400); // ถ้าไม่มีข้อความคอมเมนต์ ส่งกลับ HTTP 400 (Bad Request)
        echo json_encode(['status' => '400', 'error' => 'Comment text is required']); // ส่งข้อความ error กลับ
        exit; // หยุดการทำงานของฟังก์ชั่น
    }

    // เตรียมคำสั่ง SQL สำหรับการอัปเดตข้อมูลคอมเมนต์ในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE Comments SET comment = ?, updated_at = CURRENT_TIMESTAMP WHERE comment_id = ?");
    $stmt->execute([$data['comment'], $data['comment_id']]); // ใช้คำสั่ง SQL ในการอัปเดตคอมเมนต์

    // ส่งผลลัพธ์ว่าการอัปเดตสำเร็จ
    http_response_code(200); // ส่งกลับ HTTP 200 (OK)
    echo json_encode(['status' => '200', 'message' => 'Comment successfully updated']); // ส่งข้อความ success กลับ
}
