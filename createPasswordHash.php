<?php
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST':
        createPasswordHash();
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => '405', 'error' => 'Invalid request method']);
        break;
}

function createPasswordHash()
{

    $data = json_decode(file_get_contents("php://input"), true);
    $password = $data['password'] ?? '';

    $plain_password = $password;
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    echo json_encode(["status" => "200", "message" => "success", "password" => $hashed_password]);
}
