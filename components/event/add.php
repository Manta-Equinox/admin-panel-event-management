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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title     = trim($_POST['name'] ?? '');
    $desc      = trim($_POST['desc'] ?? '');
    $type      = trim($_POST['type'] ?? '');
    $date      = trim($_POST['date'] ?? '');
    $time      = trim($_POST['time'] ?? '');
    $location  = trim($_POST['location'] ?? '');

    if ($title === '' || $desc === '' || $type === '' || $date === '' || $time === '' || $location === '') {
        $fmsg = "Please fill all required fields.";
    } else {

        $stmt = $dbc->prepare("
            INSERT INTO events (title, description, event_type, event_date, event_time, location)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssss",
            $title,
            $desc,
            $type,
            $date,
            $time,
            $location
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

    <div class="d-flex align-items-center mb-3">
        <h4 class="fw-bold">
            <i class='bx bx-calendar-plus'></i> Add New Event
        </h4>
    </div>

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger">
            <?php echo $fmsg; ?>
        </div>
    <?php } ?>

    <form method="post">

        <div class="form-group mb-2">
            <label>Title</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="form-group mb-2">
            <label>Description</label>
            <input type="text" class="form-control" name="desc" required>
        </div>

        <div class="form-group mb-2">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="public">Public</option>
                <option value="private">Private</option>
            </select>
        </div>

        <div class="form-group mb-2">
            <label>Date</label>
            <input type="date" class="form-control" name="date" required>
        </div>

        <div class="form-group mb-2">
            <label>Time</label>
            <input type="time" class="form-control" name="time" required>
        </div>

        <div class="form-group mb-3">
            <label>Location</label>
            <input type="text" class="form-control" name="location" required>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class='bx bx-plus'></i> Add Event
        </button>

    </form>

</div>

</section>

<?php include_once('../../templates/footer.php'); ?>

</body>
</html>