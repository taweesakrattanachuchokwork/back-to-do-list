<?php
require 'middleware.php'; // เรียกใช้งาน Middleware สำหรับตรวจสอบ Token
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken(); // ฟังก์ชั่นนี้จะตรวจสอบความถูกต้องของ Token เพื่อยืนยันตัวตนของผู้ใช้

header("Content-Type: application/json"); // กำหนดประเภทข้อมูลที่จะส่งกลับเป็น JSON

$method = $_SERVER["REQUEST_METHOD"]; // รับค่าของ HTTP method (GET, POST, PUT, DELETE) ที่ใช้ในการร้องขอ

// ใช้ switch เพื่อเลือกทำงานตาม HTTP method
switch ($method) {
    case "DELETE":
        deleteDetail($conn); // หากเป็น DELETE method ให้เรียกฟังก์ชั่น deleteDetail
        break;
    default:
        http_response_code(405); // ถ้าไม่ใช่ method ที่รองรับ ให้ส่งกลับ HTTP 405 (Method Not Allowed)
        echo json_encode(["message" => "Method not allowed"]); // ส่งข้อความ error กลับ
}

// ฟังก์ชั่นสำหรับลบคอมเมนต์
function deleteDetail($conn)
{
    // รับข้อมูลจาก request body ที่ส่งมา
    $data = json_decode(file_get_contents("php://input"), true); // แปลงข้อมูล JSON จาก request body เป็น array

    // ตรวจสอบว่า comment_id มีข้อมูลหรือไม่
    if (!isset($data["comment_id"]) || empty($data["comment_id"])) {
        http_response_code(400); // ถ้าไม่มี comment_id ส่งกลับ HTTP 400 (Bad Request)
        echo json_encode(["status" => "400", "error" => "Comment ID is required"]); // ส่งข้อความ error กลับ
        exit; // หยุดการทำงานของฟังก์ชั่น
    }

    // ตรวจสอบว่า comment_id มีอยู่ในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Comments WHERE comment_id = ?");
    $stmt->execute([$data["comment_id"]]); // ใช้คำสั่ง SQL เพื่อตรวจสอบว่ามี comment_id นั้นในฐานข้อมูลหรือไม่
    $commentExists = $stmt->fetchColumn(); // ดึงค่าผลลัพธ์ที่ได้จากการคำนวณจำนวนแถวที่ตรงกัน

    // หากไม่มี comment_id ในฐานข้อมูล
    if ($commentExists == 0) {
        http_response_code(200); // ส่งกลับ HTTP 200 (OK) เพราะไม่พบคอมเมนต์
        echo json_encode(["status" => "200", "message" => "Comment not found"]); // ส่งข้อความว่าไม่พบคอมเมนต์
        return; // หยุดการทำงานของฟังก์ชั่น
    }

    // ลบคอมเมนต์
    $stmt = $conn->prepare("DELETE FROM Comments WHERE comment_id = ?");
    $stmt->execute([$data["comment_id"]]); // ใช้คำสั่ง SQL ในการลบคอมเมนต์ที่มี comment_id ตามที่ระบุ

    http_response_code(200); // ส่งกลับ HTTP 200 (OK)
    echo json_encode(["status" => "200", "message" => "Comment deleted successfully"]); // ส่งข้อความ success กลับ
}
