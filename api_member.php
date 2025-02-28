<?php
require 'middleware.php'; // เรียกใช้งาน Middleware
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getUser($conn);
        break;
    case 'POST':
        createUser($conn);
        break;
    case 'PUT':
        updateUser($conn);
        break;
    case 'DELETE':
        deleteUser($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

// ฟังก์ชัน GET สำหรับการดึงข้อมูลผู้ใช้
function getUser($conn)
{
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $stmt = $conn->prepare("SELECT user_id, username, email, created_at, updated_at FROM User WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "User not found"]
        ];
        echo json_encode($response);
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, email, created_at, updated_at FROM User");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "User not found"]
        ];
        echo json_encode($response);
    }
}

// ฟังก์ชัน POST สำหรับการสร้างผู้ใช้ใหม่
function createUser($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['username'], $data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Missing required fields"]);
        return;
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Invalid email format"]);
        return;
    }

    // ตรวจสอบความแข็งแกร่งของรหัสผ่าน
    $password = $data['password'];
    if (!preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Password must be at least 8 characters long and include at least one number, one lowercase letter, one uppercase letter, and one special character."]);
        return;
    }


    // ตรวจสอบว่า user_id มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user_exists = $stmt->fetchColumn();

    // ตรวจสอบว่า username นี้มีอยู่ในฐานข้อมูลหรือไม่
    if ($user_exists > 0) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Username already exists"]);
        return;
    }




    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");
    $success = $stmt->execute([$data['username'], $data['email'], $hashedPassword]);

    echo json_encode(["status" => 200, "message" => $success ? "User created successfully" : "Error creating user"]);
}

// ฟังก์ชัน PUT สำหรับการอัปเดตข้อมูลผู้ใช้
function updateUser($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id'], $data['username'], $data['email'])) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Missing required fields"]);
        return;
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Invalid email format"]);
        return;
    }

    // ตรวจสอบความแข็งแกร่งของรหัสผ่าน
    $password = $data['new_password'];
    if (!preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Password must be at least 8 characters long and include at least one number, one lowercase letter, one uppercase letter, and one special character."]);
        return;
    }

    // ตรวจสอบว่า user_id มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE username = ? and user_id != ?");
    $stmt->execute([$data['username'], $data['user_id']]);
    $user_exists = $stmt->fetchColumn();

    // ตรวจสอบว่า username นี้มีอยู่ในฐานข้อมูลหรือไม่
    if ($user_exists > 0) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Username already exists"]);
        return;
    }

    // เริ่มต้นคำสั่ง SQL สำหรับการอัปเดต
    $query = "UPDATE User SET username = ?, email = ?";

    // หากมีการส่งรหัสผ่านใหม่เข้ามาให้เพิ่มคำสั่งสำหรับการอัปเดตรหัสผ่าน
    if (isset($data['new_password']) && !empty($data['new_password'])) {
        $new_password = password_hash($data['new_password'], PASSWORD_BCRYPT); // การเข้ารหัสรหัสผ่านใหม่
        $query .= ", password = ?"; // เพิ่มคำสั่ง SQL สำหรับการอัปเดตรหัสผ่าน
    }

    // ต่อคำสั่ง SQL กับเงื่อนไข WHERE user_id
    $query .= " WHERE user_id = ?";

    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare($query);

    // ข้อมูลที่ใช้ใน execute
    $params = [$data['username'], $data['email']];

    // ถ้ามีการส่งรหัสผ่านใหม่เข้ามาให้เพิ่มรหัสผ่านในพารามิเตอร์
    if (isset($new_password)) {
        $params[] = $new_password;
    }

    // เพิ่ม user_id เป็นพารามิเตอร์สุดท้าย
    $params[] = $data['user_id'];

    // ดำเนินการ execute คำสั่ง SQL
    $success = $stmt->execute($params);

    // ส่งผลลัพธ์เป็น JSON
    echo json_encode(["message" => $success ? "User updated successfully" : "Error updating user"]);
}

// ฟังก์ชัน DELETE สำหรับการลบผู้ใช้
function deleteUser($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    // ตรวจสอบว่า user_id ถูกส่งมาหรือไม่
    if (!isset($data['user_id']) || empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "User ID is required"]);
        return;
    }

    // ตรวจสอบว่า user_id มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    $user_exists = $stmt->fetchColumn();

    if ($user_exists == 0) {
        http_response_code(200);
        echo json_encode(["status" => 200, "message" => "User not found"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM User WHERE user_id = ?");
    $success = $stmt->execute([$data['user_id']]);

    echo json_encode(["status" => 200, "message" => $success ? "User deleted successfully" : "Error deleting user"]);
}
