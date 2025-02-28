<?php
require 'middleware.php'; // เรียกใช้งาน Middleware เพื่อเช็คการยืนยันตัวตนของผู้ใช้
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken(); // ตรวจสอบและยืนยันตัวตนของผู้ใช้จาก Token

header('Content-Type: application/json'); // กำหนดประเภทของข้อมูลที่ส่งกลับเป็น JSON

$method = $_SERVER['REQUEST_METHOD']; // รับ HTTP method (GET, POST, PUT, DELETE) ที่ใช้ใน request

switch ($method) {
    case 'GET': // ถ้าเป็น GET method
        getUser($conn); // เรียกฟังก์ชั่นสำหรับดึงข้อมูลผู้ใช้
        break;
    case 'POST': // ถ้าเป็น POST method
        createUser($conn); // เรียกฟังก์ชั่นสำหรับสร้างผู้ใช้ใหม่
        break;
    case 'PUT': // ถ้าเป็น PUT method
        updateUser($conn); // เรียกฟังก์ชั่นสำหรับอัปเดตข้อมูลผู้ใช้
        break;
    case 'DELETE': // ถ้าเป็น DELETE method
        deleteUser($conn); // เรียกฟังก์ชั่นสำหรับลบผู้ใช้
        break;
    default:
        http_response_code(405); // ถ้าไม่ใช่ GET, POST, PUT, หรือ DELETE จะส่งกลับ 405 Method Not Allowed
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

// ฟังก์ชัน GET สำหรับการดึงข้อมูลผู้ใช้
function getUser($conn)
{
    if (isset($_GET['user_id'])) { // ถ้ามีการส่ง user_id มาใน URL
        $user_id = $_GET['user_id']; // รับค่าจาก URL
        $stmt = $conn->prepare("SELECT user_id, username, email, created_at, updated_at FROM User WHERE user_id = ?");
        $stmt->execute([$user_id]); // ดึงข้อมูลของผู้ใช้ที่มี user_id นี้
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // ดึงข้อมูลมาในรูปแบบ associative array
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "User not found"] // ถ้าไม่พบผู้ใช้ ให้ส่งข้อความว่าไม่พบ
        ];
        echo json_encode($response); // ส่งกลับข้อมูลในรูปแบบ JSON
    } else { // ถ้าไม่มีการส่ง user_id
        $stmt = $conn->prepare("SELECT user_id, username, email, created_at, updated_at FROM User");
        $stmt->execute(); // ดึงข้อมูลของผู้ใช้ทั้งหมด
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // ดึงข้อมูลทั้งหมดมาในรูปแบบ associative array
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "User not found"] // ถ้าไม่พบผู้ใช้ ให้ส่งข้อความว่าไม่พบ
        ];
        echo json_encode($response); // ส่งกลับข้อมูลในรูปแบบ JSON
    }
}

// ฟังก์ชัน POST สำหรับการสร้างผู้ใช้ใหม่
function createUser($conn)
{
    $data = json_decode(file_get_contents("php://input"), true); // รับข้อมูลจาก body request

    if (!isset($data['username'], $data['email'], $data['password'])) { // ตรวจสอบว่ามีข้อมูลที่จำเป็นหรือไม่
        http_response_code(400); // ถ้าไม่มีข้อมูลที่จำเป็น ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Missing required fields"]);
        return;
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // ถ้าอีเมลไม่ถูกต้อง ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Invalid email format"]);
        return;
    }

    // ตรวจสอบความแข็งแกร่งของรหัสผ่าน
    $password = $data['password'];
    if (!preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
        http_response_code(400); // ถ้ารหัสผ่านไม่ตรงตามเงื่อนไข ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Password must be at least 8 characters long and include at least one number, one lowercase letter, one uppercase letter, and one special character."]);
        return;
    }

    // ตรวจสอบว่า username มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user_exists = $stmt->fetchColumn();

    if ($user_exists > 0) { // ถ้า username นี้มีอยู่ในระบบแล้ว
        http_response_code(400); // ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Username already exists"]);
        return;
    }

    // เข้ารหัสรหัสผ่านก่อนเก็บลงฐานข้อมูล
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");
    $success = $stmt->execute([$data['username'], $data['email'], $hashedPassword]); // สร้างผู้ใช้ใหม่

    echo json_encode(["status" => 200, "message" => $success ? "User created successfully" : "Error creating user"]);
}

// ฟังก์ชัน PUT สำหรับการอัปเดตข้อมูลผู้ใช้
function updateUser($conn)
{
    $data = json_decode(file_get_contents("php://input"), true); // รับข้อมูลจาก body request

    if (!isset($data['user_id'], $data['username'], $data['email'])) { // ตรวจสอบว่ามีข้อมูลที่จำเป็นหรือไม่
        http_response_code(400); // ถ้าไม่มีข้อมูลที่จำเป็น ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Missing required fields"]);
        return;
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // ถ้าอีเมลไม่ถูกต้อง ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Invalid email format"]);
        return;
    }

    // ตรวจสอบความแข็งแกร่งของรหัสผ่าน
    $password = $data['new_password'];
    if (!preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
        http_response_code(400); // ถ้ารหัสผ่านใหม่ไม่ตรงตามเงื่อนไข ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "Password must be at least 8 characters long and include at least one number, one lowercase letter, one uppercase letter, and one special character."]);
        return;
    }

    // ตรวจสอบว่า username นี้มีในระบบหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE username = ? and user_id != ?");
    $stmt->execute([$data['username'], $data['user_id']]);
    $user_exists = $stmt->fetchColumn();

    if ($user_exists > 0) { // ถ้า username นี้มีอยู่ในระบบแล้ว
        http_response_code(400); // ส่งกลับ 400 Bad Request
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
    $data = json_decode(file_get_contents("php://input"), true); // รับข้อมูลจาก body request

    // ตรวจสอบว่า user_id ถูกส่งมาหรือไม่
    if (!isset($data['user_id']) || empty($data['user_id'])) {
        http_response_code(400); // ถ้าไม่มี user_id ให้ส่งกลับ 400 Bad Request
        echo json_encode(["status" => 400, "message" => "User ID is required"]);
        return;
    }

    // ตรวจสอบว่า user_id มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    $user_exists = $stmt->fetchColumn();

    if ($user_exists == 0) { // ถ้าไม่พบ user_id นี้ในฐานข้อมูล
        http_response_code(200); // ส่งกลับ 200 OK แต่ไม่พบข้อมูล
        echo json_encode(["status" => 200, "message" => "User not found"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM User WHERE user_id = ?");
    $success = $stmt->execute([$data['user_id']]); // ลบผู้ใช้จากฐานข้อมูล

    echo json_encode(["status" => 200, "message" => $success ? "User deleted successfully" : "Error deleting user"]);
}
