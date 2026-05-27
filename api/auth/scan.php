<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit();
}

$token = trim($_POST['token'] ?? '');
$scanned_by = intval($_POST['scanned_by'] ?? 0);

if ($token === '' || $scanned_by === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing data"
    ]);
    exit();
}

$sql = "SELECT event_id, participant_id 
        FROM qr_tokens 
        WHERE token = ? 
        LIMIT 1";

$stmt = $dbc->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid QR code"
    ]);
    exit();
}

$row = $result->fetch_assoc();
$event_id = $row['event_id'];
$participant_id = $row['participant_id'];

$check = $dbc->prepare("
    SELECT COUNT(*) as cnt 
    FROM attendance_logs 
    WHERE event_id = ? AND participant_id = ?
");

$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$checkRes = $check->get_result()->fetch_assoc();

if ($checkRes['cnt'] > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Already scanned"
    ]);
    exit();
}

$insert = $dbc->prepare("
    INSERT INTO attendance_logs (event_id, participant_id, scanned_by)
    VALUES (?, ?, ?)
");

$insert->bind_param("iii", $event_id, $participant_id, $scanned_by);

if ($insert->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Attendance recorded"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed"
    ]);
}
?>