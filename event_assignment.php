<?php
session_start();
require_once __DIR__ ."/api/config/db.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['assign'])) {
    $event_id = intval($_POST['event_id']);
    $staff_id = intval($_POST['staff_id']);

    if ($event_id > 0 && $staff_id > 0) {

        $check = $dbc->prepare("
            SELECT assignment_id 
            FROM event_assignments 
            WHERE event_id = ? AND staff_id = ?
        ");
        $check->bind_param("ii", $event_id, $staff_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {

            $stmt = $dbc->prepare("
                INSERT INTO event_assignments (event_id, staff_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $event_id, $staff_id);
            $stmt->execute();

            $message = "Staff assigned successfully.";
        } else {
            $error = "This staff is already assigned to this event.";
        }
    } else {
        $error = "Please select both event and staff.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $del = $dbc->prepare("
        DELETE FROM event_assignments 
        WHERE assignment_id = ?
    ");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: event_assignment.php");
    exit();
}

$events = $dbc->query("SELECT event_id, event_name FROM events");

$staff = $dbc->query("SELECT staff_id, name FROM staff_users");

$assignments = $dbc->query("
    SELECT 
        ea.assignment_id,
        e.event_name,
        s.name AS staff_name,
        ea.assigned_at
    FROM event_assignments ea
    JOIN events e ON ea.event_id = e.event_id
    JOIN staff_users s ON ea.staff_id = s.staff_id
    ORDER BY ea.assigned_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Assignment</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .container { width: 90%; margin: 30px auto; }
        .box { background:#fff; padding:20px; border-radius:8px; margin-bottom:20px; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:10px; border:1px solid #ddd; text-align:left; }
        th { background:#333; color:#fff; }
        select, button { padding:8px; margin:5px 0; width:100%; }
        .btn { background:#007bff; color:#fff; border:none; cursor:pointer; }
        .btn:hover { background:#0056b3; }
        .delete { color:red; text-decoration:none; }
        .msg { color:green; }
        .err { color:red; }
    </style>
</head>
<body>

<div class="container">

    <div class="box">
        <h2>Event Assignment</h2>

        <?php if (!empty($message)) echo "<p class='msg'>$message</p>"; ?>
        <?php if (!empty($error)) echo "<p class='err'>$error</p>"; ?>

        <form method="POST">
            <label>Event</label>
            <select name="event_id" required>
                <option value="">Select Event</option>
                <?php while ($e = $events->fetch_assoc()): ?>
                    <option value="<?= $e['event_id'] ?>">
                        <?= $e['event_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Staff</label>
            <select name="staff_id" required>
                <option value="">Select Staff</option>
                <?php while ($s = $staff->fetch_assoc()): ?>
                    <option value="<?= $s['staff_id'] ?>">
                        <?= $s['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="assign" class="btn">Assign Staff</button>
        </form>
    </div>

    <div class="box">
        <h2>Assigned Staff List</h2>

        <table>
            <tr>
                <th>Event</th>
                <th>Staff</th>
                <th>Assigned Date</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['event_name'] ?></td>
                    <td><?= $row['staff_name'] ?></td>
                    <td><?= $row['assigned_at'] ?></td>
                    <td>
                        <a class="delete" href="?delete=<?= $row['assignment_id'] ?>"
                           onclick="return confirm('Delete this assignment?')">
                           Remove
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>
    </div>

</div>

</body>
</html>