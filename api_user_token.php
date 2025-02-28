<?php
require 'vendor/autoload.php'; // โหลดไลบรารีที่ติดตั้งด้วย Composer
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST':
        createToken($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']);
        break;
}


function createToken($conn)
{
    $secret_key = "todolist@2024_secret!API#JWT";
    $issued_at = time();
    $expiration_time = $issued_at + 3600; // Token หมดอายุใน 1 ชั่วโมง

    // รับค่า JSON จาก Postman
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "400", "message" =>  "error", "message" => "Username and password are required"]);
        exit;
    }

    // ตรวจสอบ username ในฐานข้อมูล
    $sql = "SELECT * FROM User WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // สร้าง JWT
            $payload = [
                "iss" => "todo_api",
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "user" => [
                    "user_id" => $user['user_id'],
                    "username" => $user['username'],
                    "email" => $user['email']
                ]
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            echo json_encode(["status" => "200", "message" => "successful", "token" => $jwt]);
        } else {
            echo json_encode(["status" => "200", "message" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "200", "message" => "error", "message" => "User not found"]);
    }
}
