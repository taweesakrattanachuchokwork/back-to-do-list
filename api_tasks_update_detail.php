<?php
require 'middleware.php'; // เรียกใช้งาน Middleware
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'PUT':
        updateDetail($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}

function updateDetail($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่า comment_id มีในข้อมูลหรือไม่
    if (!isset($data['comment_id']) || empty($data['comment_id'])) {
        http_response_code(400);
        echo json_encode(['status' => '400', 'error' => 'comment_id is required']);
        exit;
    }

    // ตรวจสอบว่า comment มีข้อมูลหรือไม่
    if (!isset($data['comment']) || empty($data['comment'])) {
        http_response_code(400);
        echo json_encode(['status' => '400', 'error' => 'Comment text is required']);
        exit;
    }

    // อัพเดตข้อมูลคอมเมนต์ในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE Comments SET comment = ?, updated_at = CURRENT_TIMESTAMP WHERE comment_id = ?");
    $stmt->execute([$data['comment'], $data['comment_id']]);

    // ส่งผลลัพธ์ว่าอัพเดตสำเร็จ
    http_response_code(200);
    echo json_encode(['status' => '200', 'message' => 'Comment successfully updated']);
}
