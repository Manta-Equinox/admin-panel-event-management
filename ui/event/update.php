<?php
session_start();

require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../home.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}

$id = (int) $_GET['id'];

$stmt = $dbc->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Update Event</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px; max-width: 800px;">

    <h2 class="mb-3">Update Event</h2>

    <div id="msg"></div>

    <form id="updateForm">

        <input type="hidden" name="event_id" value="<?= $id ?>">

        <div class="mb-2">
            <label>Title</label>
            <input type="text" class="form-control" name="title"
                   value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Description</label>
            <textarea class="form-control" name="description"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="mb-2">
            <label>Event Date</label>
            <input type="date" class="form-control" name="event_date"
                   value="<?= $event['event_date'] ?>">
        </div>

        <div class="mb-2">
            <label>Start Time</label>
            <input type="time" class="form-control" name="start_time"
                   value="<?= $event['start_time'] ?>">
        </div>

        <div class="mb-2">
            <label>End Time</label>
            <input type="time" class="form-control" name="end_time"
                   value="<?= $event['end_time'] ?>">
        </div>

        <div class="mb-2">
            <label>Location</label>
            <input type="text" class="form-control" name="location"
                   value="<?= htmlspecialchars($event['location']) ?>">
        </div>

        <div class="mb-2">
            <label>Capacity</label>
            <input type="number" class="form-control" name="capacity"
                   value="<?= (int)$event['capacity'] ?>">
        </div>

        <div class="mb-2">
            <label>Status</label>
            <select class="form-select" name="status">
                <option value="pending" <?= $event['status']=='pending'?'selected':'' ?>>Pending</option>
                <option value="approved" <?= $event['status']=='approved'?'selected':'' ?>>Approved</option>
                <option value="cancelled" <?= $event['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3 w-100">
            Update Event
        </button>

    </form>

</div>

</section>

<?php include_once('../../templates/footer.php'); ?>

<script>
document.getElementById("updateForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const res = await fetch("../../api/events/update.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();

    const msgBox = document.getElementById("msg");

    if (data.success) {
        msgBox.innerHTML = `<div class="alert alert-success">Event updated successfully</div>`;
        setTimeout(() => {
            window.location.href = "../../events.php";
        }, 1000);
    } else {
        msgBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
    }
});
</script>

</body>
</html>