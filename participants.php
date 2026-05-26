<?php
session_start();
require_once __DIR__ . "/connect.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

$stmt = $dbc->prepare("
    SELECT event_id, title, status
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$stmt = $dbc->prepare("
    SELECT 
        id,
        name,
        email,
        status,
        joined_at
    FROM event_participants
    WHERE event_id = ?
    ORDER BY joined_at DESC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$participants = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Participants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h3>Participants</h3>

<p><b>Event:</b> <?= htmlspecialchars($event['title']) ?></p>

<?php if ($event['status'] === 'approved') { ?>
    <div class="alert alert-success">
        This event is open for participation.
    </div>
<?php } else { ?>
    <div class="alert alert-warning">
        Event is not currently active.
    </div>
<?php } ?>

<div class="table-responsive mt-3">

<table class="table table-striped table-hover align-middle">

    <thead class="table-dark">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Joined At</th>
        </tr>
    </thead>

    <tbody>

    <?php if ($participants->num_rows > 0) { ?>

        <?php while ($p = $participants->fetch_assoc()) { ?>

            <tr>
                <td><?= htmlspecialchars($p['name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($p['email'] ?? 'N/A') ?></td>

                <td>
                    <span class="badge bg-<?= $p['status'] === 'attended' ? 'success' : 'primary' ?>">
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </td>

                <td>
                    <?= $p['joined_at'] 
                        ? date("M d, Y h:i A", strtotime($p['joined_at'])) 
                        : '-' ?>
                </td>
            </tr>

        <?php } ?>

    <?php } else { ?>

        <tr>
            <td colspan="4" class="text-center text-muted">
                No participants yet
            </td>
        </tr>

    <?php } ?>

    </tbody>
</table>

</div>

</body>
</html>