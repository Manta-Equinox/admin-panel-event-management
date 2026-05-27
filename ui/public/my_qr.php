<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = (int)($_GET['event_id'] ?? 0);
$participant_id = (int)$_SESSION['Pid'];

if ($event_id <= 0) {
    die("Invalid event");
}

$stmt = $dbc->prepare("
    SELECT qt.token, e.title, e.event_date, e.location
    FROM qr_tokens qt
    JOIN events e ON e.event_id = qt.event_id
    WHERE qt.event_id = ? AND qt.participant_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No QR found. Please join the event first.");
}

$data = $result->fetch_assoc();

$token = $data['token'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($token);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My QR</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            padding: 40px;
        }
        .card {
            display: inline-block;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        img {
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2><?= htmlspecialchars($data['title']) ?></h2>
    <p><?= $data['event_date'] ?> | <?= htmlspecialchars($data['location']) ?></p>

    <img src="<?= $qrUrl ?>" width="250" height="250">

    <p><b>Token:</b> <?= $token ?></p>
</div>

</body>
</html>