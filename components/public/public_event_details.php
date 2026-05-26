<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

date_default_timezone_set("Asia/Manila");

$participant_id = (int) $_SESSION['Pid'];
$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

if (isset($_POST['cancel'])) {

    $delQR = $dbc->prepare("
        DELETE FROM qr_tokens
        WHERE event_id = ? AND participant_id = ?
    ");
    $delQR->bind_param("ii", $event_id, $participant_id);
    $delQR->execute();

    $delEP = $dbc->prepare("
        DELETE FROM event_participants
        WHERE event_id = ? AND participant_id = ?
    ");
    $delEP->bind_param("ii", $event_id, $participant_id);
    $delEP->execute();

    header("Location: public_event.php?msg=cancelled");
    exit();
}

$stmt = $dbc->prepare("
    SELECT event_id, title, description, event_date, start_time, end_time, location
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

$now = new DateTime("now");

$startTimeStr = $event['start_time'] ?? '00:00:00';
$endTimeStr   = $event['end_time'] ?? '23:59:59';

$start = new DateTime($event['event_date'] . ' ' . $startTimeStr);
$end   = new DateTime($event['event_date'] . ' ' . $endTimeStr);

if ($end <= $start) {
    $end->modify('+1 day');
}

$isEnded = ($now > $end);

if ($now < $start) {
    $eventStatus = "<span class='badge bg-info'>Upcoming</span>";
} elseif ($now >= $start && $now <= $end) {
    $eventStatus = "<span class='badge bg-success'>Ongoing</span>";
} else {
    $eventStatus = "<span class='badge bg-secondary'>Ended</span>";
}

$check = $dbc->prepare("
    SELECT id
    FROM event_participants
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$already_joined = $check->get_result()->num_rows > 0;

$qrCode = null;
$name = '';

if ($already_joined) {

    $stmt = $dbc->prepare("
        SELECT p.name, qt.qr_code
        FROM event_participants ep
        JOIN participants p ON p.participant_id = ep.participant_id
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

    $qrCode = $data['qr_code'] ?? null;
    $name = $data['name'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f6fa; }
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

    <h3><?= htmlspecialchars($event['title']) ?></h3>

    <div class="mb-2">
        <?= $eventStatus ?>
    </div>

    <p><?= htmlspecialchars($event['description']) ?></p>

    <p><b>Date:</b> <?= $event['event_date'] ?></p>
    <p><b>Location:</b> <?= htmlspecialchars($event['location']) ?></p>

    <hr>

    <?php if (!$already_joined): ?>

        <div class="alert alert-info">
            You have not joined this event yet.
        </div>

        <?php if (!$isEnded): ?>
            <form method="POST" action="public_participate.php">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <button type="submit" class="btn btn-primary w-100">
                    Participate
                </button>
            </form>
        <?php else: ?>
            <button class="btn btn-secondary w-100" disabled>
                Event Ended
            </button>
        <?php endif; ?>

    <?php else: ?>

        <p><b>Participant:</b> <?= htmlspecialchars($name) ?></p>

        <hr>

        <?php if ($qrCode): ?>

            <div class="text-center">
                <img id="qrImage" src="<?= htmlspecialchars($qrCode) ?>" class="qr-img mb-3">

                <?php if ($isEnded): ?>
                    <button class="btn btn-secondary w-100" disabled>
                        QR Disabled (Event Ended)
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="downloadQR()">
                        Save QR Code
                    </button>
                <?php endif; ?>
            </div>

            <?php if (!$isEnded): ?>
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
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-warning">
                QR Code not generated yet.
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-3"
              onsubmit="return confirm('Cancel participation?');">
            <button type="submit" name="cancel" class="btn btn-danger w-100">
                Cancel Participation
            </button>
        </form>

    <?php endif; ?>

    <a href="public_event.php" class="btn btn-secondary w-100 mt-2">
        Back
    </a>

</div>

</body>
</html>