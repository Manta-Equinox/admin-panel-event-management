<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['Aname']) && !isset($_SESSION['Pname'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$event_id       = (int)($_POST['event_id'] ?? 0);
$participant_id = (int)($_POST['participant_id'] ?? ($_SESSION['Pid'] ?? 0));
$scanned_by     = $_SESSION['Aid'] ?? null;

if ($event_id <= 0 || $participant_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$check = $dbc->prepare("
    SELECT log_id 
    FROM attendance_logs 
    WHERE event_id = ? AND participant_id = ?
");

$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Already scanned"]);
    exit();
}

$stmt = $dbc->prepare("
    INSERT INTO attendance_logs 
    (event_id, participant_id, scanned_by)
    VALUES (?, ?, ?)
");

$stmt->bind_param("iii", $event_id, $participant_id, $scanned_by);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();