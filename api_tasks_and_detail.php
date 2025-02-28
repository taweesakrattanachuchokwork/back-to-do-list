<?php
require 'middleware.php'; // เรียกใช้งาน Middleware เพื่อตรวจสอบ Token
require 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบ Token ก่อนให้เข้าถึง API
$user = verifyToken(); // ฟังก์ชั่นนี้จะตรวจสอบ Token เพื่อยืนยันตัวตนของผู้ใช้

header('Content-Type: application/json'); // กำหนดประเภทของข้อมูลที่ส่งกลับเป็น JSON

$method = $_SERVER['REQUEST_METHOD']; // รับ HTTP method (GET, POST, PUT, DELETE) ที่ใช้ใน request

switch ($method) {
    case 'GET': // ถ้าเป็น GET method
        getTasks_and_detail($conn); // เรียกฟังก์ชั่นเพื่อดึงข้อมูล task และ comments
        break;
    default:
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']); // ถ้าไม่ใช่ GET method ให้ส่งกลับ error
        http_response_code(405); // ส่งกลับ HTTP 405 Method Not Allowed
        break;
}

// ฟังก์ชั่นสำหรับดึงข้อมูล Tasks และ Comments
function getTasks_and_detail($conn)
{
    // ตรวจสอบว่า task_id ถูกส่งมาใน URL หรือไม่
    $task_id = $_GET['task_id'];

    if (isset($task_id)) {
        // ถ้ามีการส่ง task_id มา จะดึงข้อมูล task และ comment ที่เกี่ยวข้องกับ task_id นั้น
        $stmt = $conn->prepare("
            SELECT 
                t.task_id, t.title, t.description, t.created_at as task_created_at, t.updated_at as task_updated_at, 
                c.comment_id, c.comment, c.created_at as comment_created_at, c.updated_at as comment_updated_at
            FROM Tasks t
            LEFT JOIN Comments c ON t.task_id = c.task_id
            WHERE t.task_id = ?
        ");
        $stmt->execute([$task_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เตรียม array สำหรับเก็บผลลัพธ์
        $response = [];
        $tasks = [];

        // จัดกลุ่มข้อมูลโดยใช้ task_id เป็น key เพื่อให้แสดง task พร้อม comments ที่เกี่ยวข้อง
        foreach ($rows as $row) {
            $task_id = $row['task_id'];

            // ถ้ายังไม่มี task_id นี้ใน $tasks ให้เพิ่มเข้าไป
            if (!isset($tasks[$task_id])) {
                $tasks[$task_id] = [
                    'task_id' => $row['task_id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'task_created_at' => $row['task_created_at'],
                    'task_updated_at' => $row['task_updated_at'],
                    'comments' => [] // เตรียม array สำหรับเก็บ comment
                ];
            }

            // ถ้ามี comment ให้เพิ่มลงใน array ของ comments
            if ($row['comment_id'] !== null) {
                $tasks[$task_id]['comments'][] = [
                    'comment_id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    'comment_created_at' => $row['comment_created_at'],
                    'comment_updated_at' => $row['comment_updated_at']
                ];
            }
        }

        // แปลงข้อมูลจาก associative array ให้อยู่ในรูปแบบ array
        $response = array_values($tasks);

        // ถ้าไม่มีข้อมูล task, ส่งข้อความว่าไม่พบ
        if (empty($response)) {
            echo json_encode(['status' => '200', 'message' => 'No tasks found']);
        } else {
            // ส่งกลับผลลัพธ์ที่ได้ในรูปแบบ JSON
            $response = [
                'status' => '200',
                'message' => 'success',
                'data' => $response
            ];
            echo json_encode($response);
        }
    } else {
        // ถ้าไม่ส่ง task_id มา จะดึงข้อมูลทั้งหมดจากตาราง Tasks
        $stmt = $conn->query("
            SELECT 
                t.task_id, t.title, t.description, t.created_at as task_created_at, t.updated_at as task_updated_at, 
                c.comment_id, c.comment, c.created_at as comment_created_at, c.updated_at as comment_updated_at
            FROM Tasks t
            LEFT JOIN Comments c ON t.task_id = c.task_id
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เตรียม array สำหรับเก็บผลลัพธ์
        $response = [];
        $tasks = [];

        // จัดกลุ่มข้อมูลโดยใช้ task_id เป็น key เพื่อให้แสดง task พร้อม comments ที่เกี่ยวข้อง
        foreach ($rows as $row) {
            $task_id = $row['task_id'];

            // ถ้ายังไม่มี task_id นี้ใน $tasks ให้เพิ่มเข้าไป
            if (!isset($tasks[$task_id])) {
                $tasks[$task_id] = [
                    'task_id' => $row['task_id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'task_created_at' => $row['task_created_at'],
                    'task_updated_at' => $row['task_updated_at'],
                    'comments' => [] // เตรียม array สำหรับเก็บ comment
                ];
            }

            // ถ้ามี comment ให้เพิ่มลงใน array ของ comments
            if ($row['comment_id'] !== null) {
                $tasks[$task_id]['comments'][] = [
                    'comment_id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    'comment_created_at' => $row['comment_created_at'],
                    'comment_updated_at' => $row['comment_updated_at']
                ];
            }
        }

        // แปลงข้อมูลจาก associative array ให้อยู่ในรูปแบบ array
        $response = array_values($tasks);

        // ถ้าไม่มีข้อมูล task, ส่งข้อความว่าไม่พบ
        if (empty($response)) {
            echo json_encode(['status' => '200', 'message' => 'No tasks found']);
        } else {
            // ส่งกลับผลลัพธ์ที่ได้ในรูปแบบ JSON
            $response = [
                'status' => '200',
                'message' => 'success',
                'data' => $response
            ];
            echo json_encode($response);
        }
    }
}
