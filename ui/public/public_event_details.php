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

if ($now < $start) {
    $eventStatus = "<span class='badge bg-info'>Upcoming</span>";
} elseif ($now <= $end) {
    $eventStatus = "<span class='badge bg-success'>Ongoing</span>";
} else {
    $eventStatus = "<span class='badge bg-secondary'>Ended</span>";
}

$isEnded = ($now > $end);

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

$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
    . urlencode($qrText);

$token = null;

$stmtToken = $dbc->prepare("
    SELECT token
    FROM qr_tokens
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$stmtToken->bind_param("ii", $event_id, $participant_id);
$stmtToken->execute();
$tokenRow = $stmtToken->get_result()->fetch_assoc();

if ($tokenRow) {
    $token = $tokenRow['token'];
}

$qrDownloadUrl = $token
    ? "../../api/qr/download_qr.php?token=" . $token
    : "#";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
}

.card-box {
    max-width: 700px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.qr-card {
    background: #fff;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.qr-title {
    font-weight: 700;
}

.qr-subtitle {
    font-size: 13px;
    color: gray;
    margin-bottom: 15px;
}

.qr-img {
    width: 220px;
    height: 220px;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 10px;
    background: #fff;
}

.qr-actions {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
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

    <div class="alert alert-info">
        You have not joined this event yet.
    </div>

    <?php if (!$isEnded): ?>
        <form method="POST" action="../../api/public/public_participate.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <button class="btn btn-primary w-100">Participate</button>
        </form>
    <?php else: ?>
        <button class="btn btn-secondary w-100" disabled>Event Ended</button>
    <?php endif; ?>

<?php else: ?>

<div class="qr-card">

    <h4 class="qr-title">Your Event QR Code</h4>

    <p class="qr-subtitle">
        Show this QR to scanner for attendance
    </p>

    <img class="qr-img" src="<?= $qrImageUrl ?>">

    <div class="qr-actions">

        <a class="btn btn-primary"
           href="<?= $qrDownloadUrl ?>">
            Download QR
        </a>

        <button class="btn btn-outline-secondary"
                onclick="copyQRText()">
            Copy QR Data
        </button>

    </div>

    <input type="text" id="qrText" value="<?= $qrText ?>" hidden>

</div>

<form method="POST" class="mt-3"
      onsubmit="return confirm('Cancel participation?');">
    <button name="cancel" class="btn btn-danger w-100">
        Cancel Participation
    </button>
</form>

<?php endif; ?>

<a href="public_event.php" class="btn btn-secondary w-100 mt-3">
    Back
</a>

</div>

<script>
function copyQRText() {
    let text = document.getElementById("qrText");
    text.hidden = false;
    text.select();
    document.execCommand("copy");
    text.hidden = true;
    alert("QR data copied!");
}
</script>

</body>
</html>