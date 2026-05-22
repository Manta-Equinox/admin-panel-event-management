<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../events.php");
    exit();
}

$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

if (isset($_GET['unassign'])) {

    $assign_id = (int) $_GET['unassign'];

    $del = $dbc->prepare("
        DELETE FROM event_assignments
        WHERE assignment_id = ? AND event_id = ?
    ");

    $del->bind_param("ii", $assign_id, $event_id);
    $del->execute();

    header("Location: assign_staff.php?id=" . $event_id);
    exit();
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
    SELECT 
        s.staff_id,
        s.name,
        sp.name AS specialization
    FROM staff_users s
    LEFT JOIN specializations sp ON s.specialization_id = sp.id
    WHERE s.role = 'employee'
");

$assignedStaff = mysqli_query($dbc, "
    SELECT 
        ea.assignment_id,
        ea.section,
        ea.assigned_at,
        s.name,
        sp.name AS specialization
    FROM event_assignments ea
    JOIN staff_users s ON ea.staff_id = s.staff_id
    LEFT JOIN specializations sp ON s.specialization_id = sp.id
    WHERE ea.event_id = $event_id
    ORDER BY ea.assigned_at DESC
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = (int) ($_POST['staff_id'] ?? 0);
    $section  = $_POST['section'] ?? '';

    if ($staff_id <= 0 || $section === '') {
        $msg = "Invalid staff selected.";
    } else {

        $check = $dbc->prepare("
            SELECT 1 
            FROM event_assignments
            WHERE event_id = ? AND section = ?
        ");

        $check->bind_param("is", $event_id, $section);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {

            $insert = $dbc->prepare("
                INSERT INTO event_assignments (event_id, staff_id, section)
                VALUES (?, ?, ?)
            ");

            $insert->bind_param("iis", $event_id, $staff_id, $section);
            $insert->execute();
            $insert->close();

            header("Location: assign_staff.php?id=" . $event_id);
            exit();

        } else {
            $msg = "This section already has an assigned staff.";
        }
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

<p><b>Event:</b> <?= htmlspecialchars($event['title']) ?></p>

<?php if (isset($msg)) { ?>
    <div class="alert alert-danger"><?= $msg ?></div>
<?php } ?>

<form method="POST">

    <div class="mb-3">
        <label>Select Section</label>
        <select name="section" class="form-control" required>
            <option value="event_manager">Event Manager</option>
            <option value="registration">Registration</option>
            <option value="security">Security</option>
            <option value="decoration">Decoration</option>
            <option value="logistics">Logistics</option>
            <option value="technical">Technical Support</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Select Employee</label>

        <select name="staff_id" class="form-control" required>
            <option value="">-- Select Staff --</option>

            <?php while ($s = mysqli_fetch_assoc($staffList)) { ?>
                <option value="<?= $s['staff_id'] ?>">
                    <?= htmlspecialchars($s['name']) ?>
                    - <?= htmlspecialchars($s['specialization'] ?? 'No Specialization') ?>
                </option>
            <?php } ?>

        </select>
    </div>

    <button class="btn btn-primary">Assign</button>
    <a href="../../events.php" class="btn btn-secondary">Back</a>

</form>

<hr class="mt-5">

<h4>Assigned Staff</h4>

<div class="table-responsive mt-3">

<table class="table table-striped table-hover align-middle">

    <thead class="table-dark">
        <tr>
            <th>Section</th>
            <th>Staff Name</th>
            <th>Specialization</th>
            <th>Assigned At</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php if (mysqli_num_rows($assignedStaff) > 0) { ?>

        <?php while ($a = mysqli_fetch_assoc($assignedStaff)) { ?>

            <tr>
                <td>
                    <span class="badge bg-primary">
                        <?= htmlspecialchars($a['section']) ?>
                    </span>
                </td>

                <td><?= htmlspecialchars($a['name']) ?></td>

                <td><?= htmlspecialchars($a['specialization'] ?? 'No Specialization') ?></td>

                <td><?= date("M d, Y h:i A", strtotime($a['assigned_at'])) ?></td>

                <td>
                    <a href="?id=<?= $event_id ?>&unassign=<?= $a['assignment_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Unassign this staff?')">
                        Unassign
                    </a>
                </td>
            </tr>

        <?php } ?>

    <?php } else { ?>

        <tr>
            <td colspan="5" class="text-center text-muted">
                No staff assigned yet
            </td>
        </tr>

    <?php } ?>

    </tbody>
</table>

</div>

</body>
</html>