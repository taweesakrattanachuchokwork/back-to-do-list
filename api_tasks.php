<?php
require 'middleware.php'; // เรียกใช้งาน Middleware สำหรับตรวจสอบ Token
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();
// $user->user->username
// $user->user->user_id;
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ใช้ switch เพื่อเลือกการทำงานตามประเภทของ HTTP request
switch ($method) {
    case 'GET':
        getTasks($conn); // เรียกฟังก์ชั่น getTasks สำหรับการดึงข้อมูล Task
        break;
    case 'POST':
        createTask($conn); // เรียกฟังก์ชั่น createTask สำหรับการสร้าง Task ใหม่
        break;
    case 'PUT':
        updateTask($conn); // เรียกฟังก์ชั่น updateTask สำหรับการอัปเดต Task ที่มีอยู่
        break;
    case 'DELETE':
        deleteTask($conn); // เรียกฟังก์ชั่น deleteTask สำหรับการลบ Task
        break;
    default:
        http_response_code(405); // ถ้าไม่ใช่ method ที่รองรับ ให้แสดง HTTP 405
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']);
        break;
}

// ฟังก์ชั่นสำหรับดึงข้อมูล Task
function getTasks($conn)
{
    // หากมีการระบุ task_id จะดึงข้อมูล Task ตาม task_id นั้น
    if (isset($_GET['task_id'])) {
        $task_id = $_GET['task_id'];
        $stmt = $conn->prepare("SELECT * FROM Tasks WHERE task_id = ?");
        $stmt->execute([$task_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // ส่งผลลัพธ์กลับในรูปแบบ JSON
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "Task not found"]
        ];

        echo json_encode($response);
    } else {
        // หากไม่มี task_id ให้ดึงข้อมูลทั้งหมดจาก Tasks
        $stmt = $conn->query("SELECT * FROM Tasks");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ส่งผลลัพธ์กลับในรูปแบบ JSON
        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "No tasks found"]
        ];

        echo json_encode($response);
    }
}

// ฟังก์ชั่นสำหรับสร้าง Task ใหม่
function createTask($conn)
{
    // รับข้อมูลจาก request body
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามีข้อมูลครบถ้วนหรือไม่
    if (!isset($data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date'])) {
        http_response_code(400); // ถ้ามีข้อมูลไม่ครบถ้วน ส่งกลับ HTTP 400
        echo json_encode(['status' => '400', 'error' => 'Missing required fields']);
        exit;
    }

    // ตรวจสอบรูปแบบของวันที่
    $date = $data['due_date'];
    if ($date) {
        $date_check = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_check || $date_check->format('Y-m-d') !== $date) {
            http_response_code(400); // ถ้ารูปแบบวันที่ไม่ถูกต้อง ส่งกลับ HTTP 400
            echo json_encode(['status' => '400', 'message' => 'Invalid date format']);
            exit;
        }
    }

    // สร้างคำสั่ง SQL สำหรับการแทรกข้อมูล Task ใหม่
    $stmt = $conn->prepare("INSERT INTO Tasks (user_id, title, description, status, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['user_id'], $data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date']]);

    // ส่งผลลัพธ์กลับในรูปแบบ JSON
    echo json_encode(['status' => '200', 'message' => 'Task created', 'task_id' => $conn->lastInsertId()]);
}

// ฟังก์ชั่นสำหรับอัปเดต Task
function updateTask($conn)
{
    // รับข้อมูลจาก request body
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามี task_id หรือไม่
    if (!isset($data['task_id'])) {
        http_response_code(400); // ถ้าไม่มี task_id ส่งกลับ HTTP 400
        echo json_encode(['status' => 400, 'error' => 'Task ID is required']);
        exit;
    }

    // ตรวจสอบรูปแบบของวันที่
    $date = $data['due_date'];
    if ($date) {
        $date_check = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_check || $date_check->format('Y-m-d') !== $date) {
            http_response_code(400); // ถ้ารูปแบบวันที่ไม่ถูกต้อง ส่งกลับ HTTP 400
            echo json_encode(['status' => '400', 'message' => 'Invalid date format']);
            exit;
        }
    }

    // สร้างคำสั่ง SQL สำหรับการอัปเดต Task
    $stmt = $conn->prepare("UPDATE Tasks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP WHERE task_id = ?");
    $stmt->execute([$data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date'], $data['task_id']]);

    // ส่งผลลัพธ์กลับในรูปแบบ JSON
    echo json_encode(['status' => '200', 'message' => 'Task updated']);
}

// ฟังก์ชั่นสำหรับลบ Task
function deleteTask($conn)
{
    // รับข้อมูลจาก request body
    $data = json_decode(file_get_contents("php://input"), true);

    // ตรวจสอบว่ามี task_id หรือไม่
    if (!isset($data['task_id']) || empty($data['task_id'])) {
        http_response_code(400); // ถ้าไม่มี task_id ส่งกลับ HTTP 400
        echo json_encode(["status" => '400', 'error' => 'Task ID is required']);
        exit;
    }

    // ตรวจสอบว่า task_id มีในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Tasks WHERE task_id = ?");
    $stmt->execute([$data['task_id']]);
    $task_exists = $stmt->fetchColumn();

    if ($task_exists == 0) {
        http_response_code(200);
        echo json_encode(["status" => "200", "message" => "Task not found"]);
        return;
    }

    try {
        // เริ่ม Transaction
        $conn->beginTransaction();

        // ลบ Comments ที่เกี่ยวข้องกับ Task ก่อน
        $stmt = $conn->prepare("DELETE FROM Comments WHERE task_id = ?");
        $stmt->execute([$data['task_id']]);

        // ลบ Task หลังจากลบ Comments แล้ว
        $stmt = $conn->prepare("DELETE FROM Tasks WHERE task_id = ?");
        $stmt->execute([$data['task_id']]);

        // ทุกอย่างเรียบร้อย ให้ Commit
        $conn->commit();
        echo json_encode(["status" => "200", "message" => "Task deleted successfully"]);
    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาด ให้ Rollback ข้อมูล
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["status" => "500", "error" => "Failed to delete task", "details" => $e->getMessage()]);
    }
}
