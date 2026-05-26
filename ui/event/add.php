<?php
session_start();

require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
    header("Location: ../../home.php");
    exit();
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

<div class="container" style="padding-top: 120px; max-width: 800px;">

    <h4 class="fw-bold mb-3">
        <i class='bx bx-calendar-plus'></i> Add New Event
    </h4>

    <div id="msg"></div>

    <form id="eventForm">

        <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>

        <textarea name="description" class="form-control mb-2" placeholder="Description" required></textarea>

        <input type="date" name="event_date" class="form-control mb-2" required>

        <input type="time" name="start_time" class="form-control mb-2" required>

        <input type="time" name="end_time" class="form-control mb-2">

        <input type="text" name="location" class="form-control mb-2" placeholder="Location" required>

        <input type="number" name="capacity" class="form-control mb-3" placeholder="Capacity" min="0">

        <button type="submit" class="btn btn-primary w-100">
            <i class='bx bx-plus'></i> Create Event
        </button>

    </form>

</div>

</section>

<?php include_once('../../templates/footer.php'); ?>

<script>
document.getElementById("eventForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    const res = await fetch("../../api/events/add.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();

    const msgBox = document.getElementById("msg");

    if (data.status === "success") {
        msgBox.innerHTML = `<div class="alert alert-success">Event created successfully</div>`;
        this.reset();
    } else {
        msgBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
    }
});
</script>

</body>
</html>