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
        getTasks_and_detail($conn);
        break;
    default:
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']);
        http_response_code(405);

        break;
}




function getTasks_and_detail($conn)
{
    $task_id = $_GET['task_id'];

    if (isset($task_id)) {

        $stmt = $conn->prepare("
                                SELECT 
                                    t.task_id, t.title, t.description, t.created_at as task_created_at,  t.updated_at as task_updated_at, 
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

        // จัดกลุ่มข้อมูลโดยใช้ task_id เป็น key
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
                    'comments' => [] // เตรียม array สำหรับ comments
                ];
            }

            // ถ้ามี comment ให้เพิ่มลงใน comments array
            if ($row['comment_id'] !== null) {
                $tasks[$task_id]['comments'][] = [
                    'comment_id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    'comment_created_at' => $row['comment_created_at'],
                    'comment_updated_at' => $row['comment_updated_at']
                ];
            }
        }

        // แปลงข้อมูลให้อยู่ในรูปแบบ array ที่ต้องการ
        $response = array_values($tasks);


        // ถ้าไม่มีข้อมูล task, ส่งข้อความว่าไม่พบ
        if (empty($response)) {
            echo json_encode(['status' => '200', 'message' => 'No tasks found']);
        } else {
            $response = [
                'status' => '200',
                'message' => 'success',
                'data' => $response
            ];
            echo json_encode($response);
        }
    } else {
        // ดึงข้อมูลทั้งหมดจากตาราง Tasks
        $stmt = $conn->query("
                                SELECT 
                                    t.task_id, t.title, t.description, t.created_at as task_created_at,  t.updated_at as task_updated_at, 
                                    c.comment_id, c.comment, c.created_at as comment_created_at, c.updated_at as comment_updated_at
                                FROM Tasks t
                                LEFT JOIN Comments c ON t.task_id = c.task_id
                                
                                ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เตรียม array สำหรับเก็บผลลัพธ์
        $response = [];
        $tasks = [];

        // จัดกลุ่มข้อมูลโดยใช้ task_id เป็น key
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
                    'comments' => [] // เตรียม array สำหรับ comments
                ];
            }

            // ถ้ามี comment ให้เพิ่มลงใน comments array
            if ($row['comment_id'] !== null) {
                $tasks[$task_id]['comments'][] = [
                    'comment_id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    'comment_created_at' => $row['comment_created_at'],
                    'comment_updated_at' => $row['comment_updated_at']
                ];
            }
        }

        // แปลงข้อมูลให้อยู่ในรูปแบบ array ที่ต้องการ
        $response = array_values($tasks);

        // ถ้าไม่มีข้อมูล task, ส่งข้อความว่าไม่พบ
        if (empty($response)) {
            echo json_encode(['status' => '200', 'message' => 'No tasks found']);
        } else {
            $response = [
                'status' => '200',
                'message' => 'success',
                'data' => $response
            ];
            echo json_encode($response);
        }
    }
}
