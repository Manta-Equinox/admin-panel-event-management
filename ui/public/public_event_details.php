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

$start = new DateTime($event['event_date'].' '.($event['start_time'] ?: '00:00:00'));
$end   = new DateTime($event['event_date'].' '.($event['end_time'] ?: '23:59:59'));

if ($end <= $start) {
    $end->modify('+1 day');
}

$isEnded = ($now > $end);

if ($now < $start) {
    $eventStatus = "<span class='badge bg-info'>Upcoming</span>";
} elseif ($now <= $end) {
    $eventStatus = "<span class='badge bg-success'>Ongoing</span>";
} else {
    $eventStatus = "<span class='badge bg-secondary'>Ended</span>";
}

$check = $dbc->prepare("
    SELECT 1
    FROM event_participants
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$already_joined = $check->get_result()->num_rows > 0;

$qrText = "event_id={$event_id}&participant_id={$participant_id}";

$qrImageUrl = $already_joined
    ? "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrText)
    : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f6fa; }

.card-box {
    max-width:600px;
    margin:50px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

.qr-img {
    width:220px;
    height:220px;
    object-fit:contain;
    border:1px solid #ddd;
    padding:10px;
    border-radius:10px;
    background:#fff;
}
</style>
</head>

<body>

<div class="card-box">

<h3><?= htmlspecialchars($event['title']) ?></h3>

<div class="mb-2"><?= $eventStatus ?></div>

<p><?= htmlspecialchars($event['description']) ?></p>
<p><b>Date:</b> <?= $event['event_date'] ?></p>
<p><b>Location:</b> <?= htmlspecialchars($event['location']) ?></p>

<hr>

<?php if (!$already_joined): ?>

    <div class="alert alert-info">You have not joined this event yet.</div>

    <?php if (!$isEnded): ?>
        <form method="POST" action="public_participate.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <button class="btn btn-primary w-100">Participate</button>
        </form>
    <?php else: ?>
        <button class="btn btn-secondary w-100" disabled>Event Ended</button>
    <?php endif; ?>

<?php else: ?>

    <hr>

    <?php if ($qrImageUrl): ?>

        <div class="text-center">

            <img src="<?= $qrImageUrl ?>" class="qr-img mb-3">

            <?php if (!$isEnded): ?>
                <a class="btn btn-primary"
                   href="<?= $qrImageUrl ?>"
                   download="event-qr.png">
                    Save QR Code
                </a>
            <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>
                    QR Disabled
                </button>
            <?php endif; ?>

        </div>

    <?php endif; ?>

    <form method="POST" class="mt-3"
          onsubmit="return confirm('Cancel participation?');">
        <button name="cancel" class="btn btn-danger w-100">
            Cancel Participation
        </button>
    </form>

<?php endif; ?>

<a href="public_event.php" class="btn btn-secondary w-100 mt-2">Back</a>

</div>

</body>
</html>