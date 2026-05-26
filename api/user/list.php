<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$query = "
    SELECT 
        staff_users.staff_id AS id,
        staff_users.name,
        staff_users.email,
        staff_users.role,
        specializations.name AS specialization
    FROM staff_users
    LEFT JOIN specializations
        ON staff_users.specialization_id = specializations.id
";

$result = mysqli_query($dbc, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => mysqli_error($dbc)]);
    exit();
}

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "email" => $row["email"],
        "role" => $row["role"],
        "specialization" => $row["specialization"] ?? "-"
    ];
}

echo json_encode($users);