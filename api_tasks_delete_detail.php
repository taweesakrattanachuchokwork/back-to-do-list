<?php
require 'middleware.php'; // เรียกใช้งาน Middleware
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "DELETE":
        deleteDetail($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}

function deleteDetail($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["comment_id"]) || empty($data["comment_id"])) {
        http_response_code(400);
        echo json_encode(["status" => "400", "error" => "Comment ID is required"]);
        exit;
    }

    // ตรวจสอบว่า comment_id มีอยู่ในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Comments WHERE comment_id = ?");
    $stmt->execute([$data["comment_id"]]);
    $commentExists = $stmt->fetchColumn();

    if ($commentExists == 0) {
        http_response_code(200);
        echo json_encode(["status" => "200", "message" => "Comment not found"]);
        return;
    }

    // ลบคอมเมนต์
    $stmt = $conn->prepare("DELETE FROM Comments WHERE comment_id = ?");
    $stmt->execute([$data["comment_id"]]);

    http_response_code(200);
    echo json_encode(["status" => "200", "message" => "Comment deleted successfully"]);
}
