<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

date_default_timezone_set("Asia/Manila");

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
        p.name,
        qt.token
    FROM event_participants ep
    INNER JOIN events e ON e.event_id = ep.event_id
    INNER JOIN participants p ON p.participant_id = ep.participant_id
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
    die("Invalid QR request");
}

$token = $data['token'] ?? null;

if (!$token) {
    die("QR not generated yet");
}

$now = new DateTime("now");

$start = new DateTime($data['event_date'].' '.($data['start_time'] ?: '00:00:00'));
$end   = new DateTime($data['event_date'].' '.($data['end_time'] ?: '23:59:59'));

if ($end <= $start) {
    $end->modify('+1 day');
}

$isEnded = ($now > $end);

$title = htmlspecialchars($data['title']);
$description = htmlspecialchars($data['description']);
$location = htmlspecialchars($data['location']);
$name = htmlspecialchars($data['name']);

$qrImage = "/enigma/api/qr/fetch.php?token=" . $token;
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
    max-width:650px;
    margin:50px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}
.qr-img {
    width:240px;
    height:240px;
    object-fit:contain;
    border:1px solid #ddd;
    padding:10px;
    border-radius:10px;
    background:#fff;
}
</style>
</head>

<body>

<div class="box">

<h3><?= $title ?></h3>

<?php if ($isEnded): ?>
    <div class="alert alert-danger">
        This event has ended. QR is no longer valid.
    </div>
<?php endif; ?>

<p><b>Description:</b> <?= $description ?></p>
<p><b>Location:</b> <?= $location ?></p>

<hr>

<p><b>Participant:</b> <?= $name ?></p>

<hr>

<div class="text-center">

    <img src="<?= $qrImage ?>" class="qr-img mb-3">

    <?php if (!$isEnded): ?>
        <button class="btn btn-primary" onclick="downloadQR()">Save QR Code</button>
    <?php else: ?>
        <button class="btn btn-secondary w-100" disabled>QR Disabled</button>
    <?php endif; ?>

</div>

<hr>

<a href="public_event.php" class="btn btn-secondary w-100 mt-2">
    Back
</a>

</div>

<script>
function downloadQR() {
    window.location.href =
        "/enigma/api/qr/download_qr.php?event_id=<?= (int)$event_id ?>";
}
</script>

</body>
</html>