<?php
require_once __DIR__ . "/../../connect.php";

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Invalid event.");
}

$event_id = intval($_GET['event_id']);

$stmt = $dbc->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    die("Event not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Required</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .card {
            max-width: 500px;
            margin: 100px auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            text-align: center;
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

<div class="card">

    <h4><?= htmlspecialchars($event['title']) ?></h4>

    <p class="text-muted mt-2">
        You need to login to participate in this event.
    </p>

    <div class="mt-4 d-grid gap-2">

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