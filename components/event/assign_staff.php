<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../events.php");
    exit();
}

$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($event_id <= 0) {
    die("Invalid event ID");
}

$sectionFilterMap = [
    "event_manager" => "manager",
    "registration"  => "registration",
    "security"      => "security",
    "decoration"    => "decoration",
    "logistics"     => "logistics",
    "technical"     => "technical"
];

$selected_section = $_GET['section'] ?? '';

$stmt = $dbc->prepare("SELECT event_id, title, event_type FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

if (isset($_GET['unassign'])) {
    $assign_id = (int) $_GET['unassign'];

    $del = $dbc->prepare("
        DELETE FROM event_assignments
        WHERE assignment_id = ? AND event_id = ?
    ");
    $del->bind_param("ii", $assign_id, $event_id);
    $del->execute();

    header("Location: assign_staff.php?id=" . $event_id . "&section=" . $selected_section);
    exit();
}

$filterSpecialization = $sectionFilterMap[$selected_section] ?? null;

if ($filterSpecialization) {
    $staffStmt = $dbc->prepare("
        SELECT s.staff_id, s.name, sp.name AS specialization
        FROM staff_users s
        LEFT JOIN specializations sp ON s.specialization_id = sp.id
        WHERE s.role = 'employee'
        AND LOWER(sp.name) LIKE CONCAT('%', ?, '%')
    ");
    $staffStmt->bind_param("s", $filterSpecialization);
} else {
    $staffStmt = $dbc->prepare("
        SELECT s.staff_id, s.name, sp.name AS specialization
        FROM staff_users s
        LEFT JOIN specializations sp ON s.specialization_id = sp.id
        WHERE s.role = 'employee'
    ");
}

$staffStmt->execute();
$staffList = $staffStmt->get_result();

$assignedStmt = $dbc->prepare("
    SELECT 
        ea.assignment_id,
        ea.section,
        ea.assigned_at,
        s.name,
        sp.name AS specialization
    FROM event_assignments ea
    JOIN staff_users s ON ea.staff_id = s.staff_id
    LEFT JOIN specializations sp ON s.specialization_id = sp.id
    WHERE ea.event_id = ?
    ORDER BY ea.assigned_at DESC
");
$assignedStmt->bind_param("i", $event_id);
$assignedStmt->execute();
$assignedStaff = $assignedStmt->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = (int)($_POST['staff_id'] ?? 0);
    $section  = trim($_POST['section'] ?? '');

    if ($staff_id <= 0 || $section === '') {
        $msg = "Invalid selection.";
    } else {

        $check = $dbc->prepare("
            SELECT 1 FROM event_assignments
            WHERE event_id = ? AND section = ?
            LIMIT 1
        ");
        $check->bind_param("is", $event_id, $section);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $msg = "Section already assigned.";
        } else {

            $insert = $dbc->prepare("
                INSERT INTO event_assignments (event_id, staff_id, section)
                VALUES (?, ?, ?)
            ");
            $insert->bind_param("iis", $event_id, $staff_id, $section);

            if ($insert->execute()) {
                header("Location: assign_staff.php?id=$event_id&section=$section");
                exit();
            } else {
                $msg = "Failed to assign staff.";
            }

            $insert->close();
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

<form method="GET" class="mb-3">
    <input type="hidden" name="id" value="<?= $event_id ?>">

    <label>Select Section</label>
    <select name="section" class="form-control" onchange="this.form.submit()">
        <option value="">-- Select Section --</option>

        <?php foreach ($sectionFilterMap as $key => $val) { ?>
            <option value="<?= $key ?>" <?= $selected_section === $key ? 'selected' : '' ?>>
                <?= ucwords(str_replace('_', ' ', $key)) ?>
            </option>
        <?php } ?>
    </select>
</form>

<form method="POST">

    <input type="hidden" name="section" value="<?= htmlspecialchars($selected_section) ?>">

    <div class="mb-3">
        <label>Select Employee</label>

        <select name="staff_id" class="form-control" required>
            <option value="">-- Select Staff --</option>

            <?php while ($s = $staffList->fetch_assoc()) { ?>
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

<table class="table table-striped mt-3">

<thead>
<tr>
    <th>Section</th>
    <th>Staff</th>
    <th>Specialization</th>
    <th>Assigned At</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while ($a = $assignedStaff->fetch_assoc()) { ?>

<tr>
    <td><span class="badge bg-primary"><?= $a['section'] ?></span></td>
    <td><?= htmlspecialchars($a['name']) ?></td>
    <td><?= htmlspecialchars($a['specialization'] ?? 'N/A') ?></td>
    <td><?= $a['assigned_at'] ?></td>
    <td>
        <a href="?id=<?= $event_id ?>&unassign=<?= $a['assignment_id'] ?>&section=<?= $selected_section ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Unassign?')">
            Unassign
        </a>
    </td>
</tr>

<?php } ?>

</tbody>
</table>

</body>
</html>