<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['Aid'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$created_by = (int)$_SESSION['Aid'];

$title      = trim($_POST['title'] ?? '');
$desc       = trim($_POST['description'] ?? '');
$type       = trim($_POST['event_type'] ?? 'public');
$date       = $_POST['event_date'] ?? '';
$start      = $_POST['start_time'] ?? '';
$end        = $_POST['end_time'] ?? '';
$location   = trim($_POST['location'] ?? '');
$capacity   = (int)($_POST['capacity'] ?? 0);

if ($title === '' || $desc === '' || $date === '' || $start === '' || $location === '') {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

$stmt = $dbc->prepare("
    INSERT INTO events 
    (title, description, event_type, event_date, start_time, end_time, location, created_by, capacity, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->bind_param(
    "sssssssii",
    $title,
    $desc,
    $type,
    $date,
    $start,
    $end,
    $location,
    $created_by,
    $capacity
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}