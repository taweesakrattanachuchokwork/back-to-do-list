<?php
require 'middleware.php'; // เรียกใช้งาน Middleware สำหรับตรวจสอบ Token
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken(); // ฟังก์ชั่นนี้จะตรวจสอบความถูกต้องของ Token เพื่อยืนยันตัวตนของผู้ใช้

header('Content-Type: application/json'); // กำหนดประเภทข้อมูลที่จะส่งกลับเป็น JSON

$method = $_SERVER['REQUEST_METHOD']; // รับค่าของ HTTP method (GET, POST, PUT, DELETE) ที่ใช้ในการร้องขอ

// ใช้ switch เพื่อเลือกทำงานตาม HTTP method
switch ($method) {
    case 'POST':
        createDetail($conn); // ถ้าเป็น POST method ให้เรียกฟังก์ชั่น createDetail
        break;
    default:
        http_response_code(405); // ถ้าไม่ใช่ method ที่รองรับ ให้ส่งกลับ HTTP 405 (Method Not Allowed)
        echo json_encode(["message" => "Method not allowed"]); // ส่งข้อความ error กลับ
}

// ฟังก์ชั่นสำหรับสร้างคอมเมนต์ใหม่
function createDetail($conn)
{
    // รับข้อมูลจาก request body ที่ส่งมา
    $data = json_decode(file_get_contents('php://input'), true); // แปลงข้อมูล JSON จาก request body เป็น array

    // ตรวจสอบว่าได้ส่งข้อมูลที่จำเป็นครบหรือไม่
    if (!isset($data['task_id'], $data['user_id'], $data['comment'])) {
        http_response_code(400); // ถ้าไม่มีข้อมูลที่จำเป็น ส่งกลับ HTTP 400 (Bad Request)
        echo json_encode(["status" => '400', 'error' => 'Missing required fields']); // ส่งข้อความ error กลับ
        exit; // หยุดการทำงานของฟังก์ชั่น
    }

    // เพิ่มข้อมูลคอมเมนต์ใหม่ในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO Comments (task_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$data['task_id'], $data['user_id'], $data['comment']]); // คำสั่ง SQL เพื่อเพิ่มข้อมูล

    // ส่งผลลัพธ์กลับไปว่าเพิ่มคอมเมนต์สำเร็จ
    http_response_code(200); // ส่งกลับ HTTP 200 (OK)
    echo json_encode(['status' => '200', 'message' => 'Comments created successfully', 'comment_id' => $conn->lastInsertId()]); // ส่งข้อความ success กลับพร้อมกับ comment_id ที่เพิ่งถูกสร้าง
}
