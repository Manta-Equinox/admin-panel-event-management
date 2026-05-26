<?php
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET['id']);

$stmt = $dbc->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$countStmt = $dbc->prepare("
    SELECT COUNT(*) AS total 
    FROM event_participants 
    WHERE event_id = ?
");
$countStmt->bind_param("i", $event_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();

$totalParticipants = $countData['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .card-box {
            max-width: 600px;
            margin: 80px auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            background: white;
        }

        .btn-primary {
            background: #5469d4;
            border: none;
        }

        .btn-primary:hover {
            background: #3f51b5;
        }
    </style>
</head>

<body>

<div class="card-box">

    <h3><?= htmlspecialchars($event['title']) ?></h3>

    <p class="text-muted">
        <?= nl2br(htmlspecialchars($event['description'])) ?>
    </p>

    <hr>

    <p><strong>Event ID:</strong> <?= $event['event_id'] ?></p>
    <p><strong>Date:</strong> <?= $event['event_date'] ?? 'TBA' ?></p>
    <p><strong>Status:</strong> <?= $event['status'] ?? 'open' ?></p>
    <p><strong>Participants:</strong> <?= $totalParticipants ?></p>

    <div class="mt-4 d-flex gap-2">

        <a href="/index.php" class="btn btn-primary">
            Login to Participate
        </a>

        <a href="public_events.php" class="btn btn-secondary">
            Back
        </a>

    </div>

</div>

</body>
</html>