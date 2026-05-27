<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

date_default_timezone_set("Asia/Manila");

$participant_id = (int) $_SESSION['Pid'];

$joined = [];

$stmt = $dbc->prepare("
    SELECT event_id 
    FROM event_participants 
    WHERE participant_id = ?
");
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $joined[$row['event_id']] = true;
}

$result = mysqli_query($dbc, "
    SELECT * 
    FROM events 
    WHERE status = 'approved'
    ORDER BY event_id DESC
");

if (!$result) {
    die("Query error: " . mysqli_error($dbc));
}

$now = new DateTime("now");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Public Events</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f6fa; }

.header {
    background:#5469d4;
    color:white;
    padding:20px;
}

.event-card {
    border:none;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    transition:0.2s;
    height:100%;
}

.event-card:hover {
    transform:translateY(-4px);
}

.small-info {
    font-size:13px;
    color:#666;
}
</style>
</head>

<body>

<div class="header d-flex justify-content-between align-items-center px-4">
    <div>
        <h2 class="m-0">Public Events</h2>
        <p class="m-0">Browse approved events</p>
    </div>
    <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
</div>

<div class="container mt-4">

<?php if (mysqli_num_rows($result) > 0): ?>
<div class="row g-3">

<?php while ($row = mysqli_fetch_assoc($result)): ?>

<?php
$event_id = (int)$row['event_id'];

$countStmt = $dbc->prepare("
    SELECT COUNT(*) AS total
    FROM event_participants
    WHERE event_id = ?
");
$countStmt->bind_param("i", $event_id);
$countStmt->execute();
$count = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

$startTime = $row['start_time'] ?: '00:00:00';
$endTime   = $row['end_time'] ?: '23:59:59';

$start = new DateTime($row['event_date'].' '.$startTime);
$end   = new DateTime($row['event_date'].' '.$endTime);

if ($end <= $start) {
    $end->modify('+1 day');
}

if ($now < $start) {
    $status = "<span class='badge bg-info'>Upcoming</span>";
} elseif ($now <= $end) {
    $status = "<span class='badge bg-success'>Ongoing</span>";
} else {
    $status = "<span class='badge bg-secondary'>Ended</span>";
}

$isEnded = ($now > $end);

$isJoined = isset($joined[$event_id]) && !$isEnded;
?>

<div class="col-md-4">
<div class="card event-card p-3 h-100">

<h5><?= htmlspecialchars($row['title']) ?></h5>
<p class="text-muted"><?= htmlspecialchars($row['description']) ?></p>

<div class="mb-2"><?= $status ?></div>

<div class="small-info mb-2">
👥 <?= $count ?> participant(s)
</div>

<hr>

<p><strong>Date:</strong> <?= $row['event_date'] ?></p>
<p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>

<a href="public_event_details.php?id=<?= $event_id ?>"
   class="btn btn-outline-secondary w-100 mb-2">
   View Details
</a>

<?php if ($isEnded): ?>

    <button class="btn btn-secondary w-100" disabled>
        Event Ended
    </button>

<?php elseif ($isJoined): ?>

    <button class="btn btn-success w-100" disabled>
        Already Joined
    </button>

<?php else: ?>

    <form method="POST" action="../../api/public/public_participate.php">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <button class="btn btn-primary w-100">
            Participate
        </button>
    </form>

<?php endif; ?>

</div>
</div>

<?php endwhile; ?>

</div>
<?php else: ?>
<p class="text-center">No approved events available.</p>
<?php endif; ?>

</div>

</body>
</html>