<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");
date_default_timezone_set("Asia/Manila");

$token = trim($_POST['token'] ?? '');
$scanned_by = (int)($_POST['staff_id'] ?? 0);

if ($token === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing token"
    ]);
    exit();
}

if ($scanned_by <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid scanner"
    ]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT 
        qt.event_id,
        qt.participant_id,
        qt.expires_at,
        e.title AS event_title,
        e.status AS event_status,
        p.name AS participant_name
    FROM qr_tokens qt
    JOIN events e ON qt.event_id = e.event_id
    JOIN participants p ON qt.participant_id = p.participant_id
    WHERE qt.token = ?
    LIMIT 1
");

$stmt->bind_param("s", $token);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid QR code"
    ]);
    exit();
}

if (strtotime($data['expires_at']) < time()) {
    echo json_encode([
        "status" => "error",
        "message" => "QR expired"
    ]);
    exit();
}


if ($data['event_status'] !== 'approved') {
    echo json_encode([
        "status" => "error",
        "message" => "Event not active"
    ]);
    exit();
}

$event_id = (int)$data['event_id'];
$participant_id = (int)$data['participant_id'];

$check = $dbc->prepare("
    SELECT attendance_id
    FROM attendance_logs
    WHERE event_id = ?
    AND participant_id = ?
    LIMIT 1
");

$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$exists = $check->get_result()->fetch_assoc();

if ($exists) {
    echo json_encode([
        "status" => "error",
        "message" => "Already marked present"
    ]);
    exit();
}


$insert = $dbc->prepare("
    INSERT INTO attendance_logs (event_id, participant_id, scanned_by)
    VALUES (?, ?, ?)
");

$insert->bind_param("iii", $event_id, $participant_id, $scanned_by);

if (!$insert->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save attendance"
    ]);
    exit();
}

echo json_encode([
    "status" => "success",
    "message" => "Attendance recorded",
    "data" => [
        "event_id" => $event_id,
        "event_title" => $data['event_title'],
        "participant_id" => $participant_id,
        "participant_name" => $data['participant_name']
    ]
]);