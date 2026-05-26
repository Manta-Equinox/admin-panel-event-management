<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$participant_id = (int)$_SESSION['Pid'];

if ($event_id <= 0) {
    die("Invalid QR request");
}

$stmt = $dbc->prepare("
    SELECT 
        e.title,
        e.description,
        e.event_date,
        e.start_time,
        e.end_time,
        e.location,
        ep.name
    FROM event_participants ep
    INNER JOIN events e ON e.event_id = ep.event_id
    WHERE ep.event_id = ?
      AND ep.participant_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Invalid QR request");
}

$title = htmlspecialchars($data['title'] ?? '');
$description = htmlspecialchars($data['description'] ?? '');
$location = htmlspecialchars($data['location'] ?? '');
$name = htmlspecialchars($data['name'] ?? '');

$date = $data['event_date'] ?? '';

$start = !empty($data['start_time']) ? date("h:i A", strtotime($data['start_time'])) : null;
$end = !empty($data['end_time']) ? date("h:i A", strtotime($data['end_time'])) : null;

$time = ($start && $end) ? "$start - $end" : ($start ?? 'Not set');

$qrText = "ENIGMA EVENT | $title | USER: $name | EVENT: $event_id";

$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrText);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f5f6fa; }
        .box {
            max-width: 650px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .qr-img {
            width: 240px;
            height: 240px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
        }
        .label { font-weight: 600; }
    </style>
</head>

<body>

<div class="box">

    <h3><?= $title ?></h3>

    <p><span class="label">Description:</span> <?= $description ?></p>
    <p><span class="label">Date:</span> <?= htmlspecialchars($date) ?></p>
    <p><span class="label">Time:</span> <?= htmlspecialchars($time) ?></p>
    <p><span class="label">Location:</span> <?= $location ?></p>

    <hr>

    <p><span class="label">Participant:</span> <?= $name ?></p>

    <hr>

    <div class="text-center">
        <img id="qr" src="<?= $qrImage ?>" class="qr-img mb-3">
        <br>
        <button class="btn btn-primary" onclick="downloadQR()">Save QR Code</button>
    </div>

    <p class="text-muted text-center mt-3">
        Show this QR code at the event entrance.
    </p>

    <a href="public_event.php" class="btn btn-secondary w-100 mt-2">
        Back
    </a>

</div>

<script>
function downloadQR() {
    const img = document.getElementById("qr");

    fetch(img.src)
        .then(res => res.blob())
        .then(blob => {
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "event_qr.png";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
}
</script>

</body>
</html>