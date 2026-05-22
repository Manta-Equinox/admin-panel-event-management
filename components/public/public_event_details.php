<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$participant_id = (int) $_SESSION['Pid'];
$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

$stmt = $dbc->prepare("
    SELECT event_id, title, description, event_date, location, status
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

$stmt = $dbc->prepare("
    SELECT ep.name, ep.email, ep.participant_id, ep.id,
           qt.qr_code
    FROM event_participants ep
    LEFT JOIN qr_tokens qt
        ON qt.event_id = ep.event_id
        AND qt.participant_id = ep.participant_id
    WHERE ep.event_id = ?
      AND ep.participant_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("You are not registered for this event.");
}

$qrCode = $data['qr_code'] ?? null;
$name = $data['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .card-box {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .qr-img {
            width: 220px;
            height: 220px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
        }
    </style>
</head>

<body>

<div class="card-box">

    <h3 class="mb-3"><?= htmlspecialchars($event['title']) ?></h3>

    <p><b>Description:</b> <?= htmlspecialchars($event['description']) ?></p>
    <p><b>Date:</b> <?= htmlspecialchars($event['event_date']) ?></p>
    <p><b>Location:</b> <?= htmlspecialchars($event['location']) ?></p>

    <hr>

    <p><b>Participant:</b> <?= htmlspecialchars($name) ?></p>

    <hr>

    <?php if ($qrCode) { ?>

        <div class="text-center">

            <img id="qrImage"
                 src="<?= htmlspecialchars($qrCode) ?>"
                 class="qr-img mb-3"
                 alt="QR Code">

            <br>

            <button class="btn btn-primary"
                    onclick="downloadQR()">
                Save QR Code
            </button>

        </div>

        <script>
            function downloadQR() {
                const link = document.createElement('a');
                link.href = document.getElementById('qrImage').src;
                link.download = "event_qr.png";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>

    <?php } else { ?>

        <div class="alert alert-warning">
            QR Code not generated yet.
        </div>

    <?php } ?>

    <a href="public_event.php" class="btn btn-secondary w-100 mt-3">
        Back
    </a>

</div>

</body>
</html>