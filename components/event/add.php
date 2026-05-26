<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../home.php");
    exit();
}

$created_by = $_SESSION['Aid'] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title        = trim($_POST['title'] ?? '');
    $desc         = trim($_POST['description'] ?? '');
    $type         = trim($_POST['event_type'] ?? 'public');
    $date         = trim($_POST['event_date'] ?? '');
    $start_time   = trim($_POST['start_time'] ?? '');
    $end_time     = trim($_POST['end_time'] ?? '');
    $location     = trim($_POST['location'] ?? '');
    $capacity     = (int)($_POST['capacity'] ?? 0);

    if (
        $title === '' ||
        $desc === '' ||
        $type === '' ||
        $date === '' ||
        $start_time === '' ||
        $location === ''
    ) {
        $fmsg = "Please fill all required fields.";
    } else {

        $stmt = $dbc->prepare("
            INSERT INTO events 
            (title, description, event_type, event_date, start_time, end_time, location, created_by, capacity, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->bind_param(
            "sssssssii",
            $title,
            $desc,
            $type,
            $date,
            $start_time,
            $end_time,
            $location,
            $created_by,
            $capacity
        );

        if ($stmt->execute()) {
            header("Location: ../../events.php");
            exit();
        } else {
            $fmsg = "Insert failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Add Event</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <h4 class="fw-bold mb-3">
        <i class='bx bx-calendar-plus'></i> Add New Event
    </h4>

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger"><?= $fmsg ?></div>
    <?php } ?>

    <form method="post">

        <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>

        <input type="text" name="description" class="form-control mb-2" placeholder="Description" required>

        <select name="event_type" class="form-control mb-2">
            <option value="public">Public</option>
            <option value="private">Private</option>
        </select>

        <input type="date" name="event_date" class="form-control mb-2" required>

        <input type="time" name="start_time" class="form-control mb-2" required>

        <input type="time" name="end_time" class="form-control mb-2">

        <input type="text" name="location" class="form-control mb-2" placeholder="Location" required>

        <input type="number" name="capacity" class="form-control mb-3" placeholder="Capacity">

        <button type="submit" class="btn btn-primary">
            <i class='bx bx-plus'></i> Create Event
        </button>

    </form>

</div>

</section>

<?php include_once('../../templates/footer.php'); ?>

</body>
</html>