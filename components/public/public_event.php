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
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $joined[$row['event_id']] = true; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f6fa; }

        .header {
            background: #5469d4;
            color: white;
            padding: 20px;
        }

        .event-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            transition: 0.2s ease;
            height: 100%;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .small-info {
            font-size: 13px;
            color: #666;
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

<?php
$query = "SELECT * FROM events WHERE status = 'approved' ORDER BY event_id DESC";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($dbc));
}

$now = new DateTime("now");

if (mysqli_num_rows($result) > 0) {
    echo '<div class="row g-3">';

    while ($row = mysqli_fetch_assoc($result)) {

        $event_id = (int)$row['event_id'];
        $already_joined = isset($joined[$event_id]);

        $countStmt = $dbc->prepare("
            SELECT COUNT(*) AS total
            FROM event_participants
            WHERE event_id = ?
        ");
        $countStmt->bind_param("i", $event_id);
        $countStmt->execute();
        $participants = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

        $startTime = $row['start_time'] ?: '00:00:00';
        $endTime   = $row['end_time'] ?: '23:59:59';

        $start = new DateTime($row['event_date'] . ' ' . $startTime);
        $end   = new DateTime($row['event_date'] . ' ' . $endTime);

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
        ?>

        <div class="col-md-4">
            <div class="card event-card p-3 h-100">

                <h5><?= htmlspecialchars($row['title']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($row['description']) ?></p>

                <div class="mb-2">
                    <?= $eventStatus ?>
                </div>

                <div class="small-info mb-2">
                    👥 <?= $participants ?> participant(s) joined
                </div>

                <hr>

                <p><strong>Date:</strong> <?= $row['event_date'] ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>

                <a href="public_event_details.php?id=<?= $event_id ?>"
                   class="btn btn-outline-secondary w-100 mb-2">
                    View Details
                </a>

                <?php if ($already_joined) { ?>

                    <button class="btn btn-success w-100" disabled>
                        Already Joined
                    </button>

                <?php } else { ?>

                    <?php if ($isEnded) { ?>
                        <button class="btn btn-secondary w-100" disabled>
                            Event Ended
                        </button>
                    <?php } else { ?>
                        <form method="POST" action="public_participate.php">
                            <input type="hidden" name="event_id" value="<?= $event_id ?>">
                            <button type="submit" class="btn btn-primary w-100">
                                Participate
                            </button>
                        </form>
                    <?php } ?>

                <?php } ?>

            </div>
        </div>

        <?php
    }

    echo '</div>';

} else {
    echo "<p class='text-center'>No approved events available.</p>";
}
?>

</div>

</body>
</html>