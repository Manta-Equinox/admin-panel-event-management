<?php
require_once __DIR__ . "/../config/db.php";

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['Aname'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$id = (int)($_POST['event_id'] ?? 0);

$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$date        = $_POST['event_date'] ?? null;
$start_time  = $_POST['start_time'] ?? null;
$end_time    = $_POST['end_time'] ?? null;
$location    = trim($_POST['location'] ?? '');
$capacity    = (int)($_POST['capacity'] ?? 0);
$status      = $_POST['status'] ?? 'pending';

$allowed_status = ['pending', 'approved', 'cancelled'];
if (!in_array($status, $allowed_status)) {
    $status = 'pending';
}

if ($id <= 0 || $title === '') {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$stmt = $dbc->prepare("
    UPDATE events
    SET title = ?, description = ?, event_date = ?, start_time = ?,
        end_time = ?, location = ?, capacity = ?, status = ?
    WHERE event_id = ?
");

if (!$stmt) {
    echo json_encode(["success" => false, "message" => $dbc->error]);
    exit();
}

$stmt->bind_param(
    "ssssssisi",
    $title,
    $description,
    $date,
    $start_time,
    $end_time,
    $location,
    $capacity,
    $status,
    $id
);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}