<?php
require 'middleware.php'; // เรียกใช้งาน Middleware
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        createDetail($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}

function createDetail($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
    if (!isset($data['task_id'], $data['user_id'], $data['comment'])) {
        http_response_code(400);
        echo json_encode(["status" => '400', 'error' => 'Missing required fields']);
        exit;
    }

    // เพิ่มข้อมูลคอมเมนต์ในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO Comments (task_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$data['task_id'], $data['user_id'], $data['comment']]);

    // ส่งผลลัพธ์กลับไปว่าเพิ่มคอมเมนต์สำเร็จ
    http_response_code(200);
    echo json_encode(['status' => '200', 'message' => 'Comments created successfully', 'comment_id' => $conn->lastInsertId()]);
}
