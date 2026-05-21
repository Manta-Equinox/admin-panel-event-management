<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../events.php");
    exit();
}

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

$stmt = $dbc->prepare("
    SELECT event_id, title, event_type
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$staffList = mysqli_query($dbc, "
    SELECT staff_id, name, email, specialization_id
    FROM staff_users
    WHERE role = 'employee'
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = isset($_POST['staff_id']) ? (int) $_POST['staff_id'] : 0;

    if ($staff_id <= 0) {
        $msg = "Invalid staff selected.";
    } else {

        $stmt = $dbc->prepare("
            SELECT 1 FROM event_assignments
            WHERE event_id = ? AND staff_id = ?
        ");
        $stmt->bind_param("ii", $event_id, $staff_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {

            $stmt = $dbc->prepare("
                INSERT INTO event_assignments (event_id, staff_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $event_id, $staff_id);
            $stmt->execute();
        }

        header("Location: ../../events.php");
        exit();
    }
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
    <p><b>Event:</b> <?= htmlspecialchars($event['title']) ?></p>
<?php } ?>

<?php if (isset($msg)) { ?>
    <div class="alert alert-danger"><?= $msg ?></div>
<?php } ?>

<form method="POST">

    <div class="form-group">
        <label>Select Employee</label>

        <select name="staff_id" class="form-control" required>
            <option value="">-- Select Staff --</option>

            <?php while ($s = mysqli_fetch_assoc($staffList)) { ?>
                <option value="<?= $s['staff_id'] ?>">
                    <?= htmlspecialchars($s['name']) ?>
                    (<?= htmlspecialchars($s['email']) ?>)
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