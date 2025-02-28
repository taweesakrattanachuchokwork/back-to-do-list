<?php
require 'middleware.php'; // เรียกใช้งาน Middleware
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken();
// $user->user->username
// $user->user->user_id;
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getTasks($conn);
        break;
    case 'POST':
        createTask($conn);
        break;
    case 'PUT':
        updateTask($conn);
        break;
    case 'DELETE':
        deleteTask($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']);
        break;
}

function getTasks($conn)
{
    if (isset($_GET['task_id'])) {
        $task_id = $_GET['task_id'];
        $stmt = $conn->prepare("SELECT * FROM Tasks WHERE task_id = ?");
        $stmt->execute([$task_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "Task not found"]
        ];

        echo json_encode($response);
    } else {
        $stmt = $conn->query("SELECT * FROM Tasks");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            "status" => 200,
            "message" => "success",
            "data" => $result ?: ["message" => "No tasks found"]
        ];

        echo json_encode($response);
    }
}

function createTask($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date'])) {
        http_response_code(400);
        echo json_encode(['status' => '400', 'error' => 'Missing required fields']);
        exit;
    }
    $date = $data['due_date'];
    if ($date) {
        $date_check = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_check || $date_check->format('Y-m-d') !== $date) {
            http_response_code(400);
            echo json_encode(['status' => '400', 'message' => 'Invalid date format']);
            exit;
        }
    }


    $stmt = $conn->prepare("INSERT INTO Tasks (user_id, title, description, status, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['user_id'], $data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date']]);
    echo json_encode(['status' => '200', 'message' => 'Task created', 'task_id' => $conn->lastInsertId()]);
}

function updateTask($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['task_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'error' => 'Task ID is required']);
        exit;
    }
    $date = $data['due_date'];
    if ($date) {
        $date_check = DateTime::createFromFormat('Y-m-d', $date);
        if (!$date_check || $date_check->format('Y-m-d') !== $date) {
            http_response_code(400);
            echo json_encode(['status' => '400', 'message' => 'Invalid date format']);
            exit;
        }
    }



    $stmt = $conn->prepare("UPDATE Tasks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP WHERE task_id = ?");
    $stmt->execute([$data['title'], $data['description'], $data['status'], $data['priority'], $data['due_date'], $data['task_id']]);
    echo json_encode(['status' => '200', 'message' => 'Task updated']);
}

function deleteTask($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['task_id']) || empty($data['task_id'])) {
        http_response_code(400);
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

    $stmt = $conn->prepare("DELETE FROM Tasks WHERE task_id = ?");
    $stmt->execute([$data['task_id']]);
    echo json_encode(['status' => '200', 'message' => 'Task deleted']);
}
