<?php
session_start();
require_once __DIR__ . "/../../connect.php";

// ONLY ADMIN CAN ACCESS
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../events.php");
    exit();
}

// GET EVENT ID
$event_id = intval($_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    die("Invalid event ID");
}

// GET EVENT INFO (optional display)
$event = mysqli_fetch_assoc(mysqli_query(
    $dbc,
    "SELECT * FROM events WHERE event_id = $event_id"
));

// GET ALL EMPLOYEES
$staffList = mysqli_query($dbc, "
    SELECT * FROM staff_users WHERE role = 'employee'
");

// ASSIGN STAFF
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = intval($_POST['staff_id']);

    // prevent duplicate assignment
    $check = mysqli_query($dbc, "
        SELECT * FROM event_assignments
        WHERE event_id = $event_id AND staff_id = $staff_id
    ");

    if (mysqli_num_rows($check) == 0) {

        mysqli_query($dbc, "
            INSERT INTO event_assignments (event_id, staff_id)
            VALUES ($event_id, $staff_id)
        ");
    }

    header("Location: ../../events.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h3>Assign Staff to Event</h3>

<?php if ($event) { ?>
    <p><b>Event:</b> <?= $event['title'] ?></p>
<?php } ?>

<form method="POST">

    <div class="form-group">
        <label>Select Employee</label>
        <select name="staff_id" class="form-control" required>
            <?php while ($s = mysqli_fetch_assoc($staffList)) { ?>
                <option value="<?= $s['staff_id'] ?>">
                    <?= $s['name'] ?> (<?= $s['email'] ?>)
                </option>
            <?php } ?>
        </select>
    </div>

    <br>

    <button class="btn btn-primary">Assign</button>
    <a href="../../events.php" class="btn btn-secondary">Back</a>

</form>

</body>
</html>