<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

require_once __DIR__ . "/connect.php";

$role = $_SESSION['role'] ?? '';
$user_id = (int)($_SESSION['Aid'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Events</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/home.css">
</head>

<body>

<?php include_once('./templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class='bx bx-calendar'></i> Events
        </h4>

        <?php if ($role === 'admin') { ?>
            <a href="./components/event/add.php" class="btn btn-primary">
                <i class='bx bx-plus'></i> Add New
            </a>
        <?php } ?>
    </div>

<?php
$query = "SELECT 
            event_id,
            title,
            description,
            event_type,
            location,
            event_date,
            start_time,
            end_time,
            status
          FROM events
          ORDER BY event_id DESC";

$exe = mysqli_query($dbc, $query);
?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Description</th>
    <th>Type</th>
    <th>Location</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while ($row = mysqli_fetch_assoc($exe)) { ?>

<?php
$event_id = (int)$row['event_id'];
$isApproved = ($row['status'] === 'approved');
?>

<tr>

    <td><?= $event_id ?></td>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars($row['description']) ?></td>

    <td>
        <span class="badge bg-primary">
            <?= ucfirst($row['event_type']) ?>
        </span>
    </td>

    <td><?= htmlspecialchars($row['location']) ?></td>

    <td><?= $row['event_date'] ?></td>

    <td>
        <?php
        $start = !empty($row['start_time']) ? date("h:i A", strtotime($row['start_time'])) : null;
        $end   = !empty($row['end_time']) ? date("h:i A", strtotime($row['end_time'])) : null;

        if ($start && $end) {
            echo "$start - $end";
        } elseif ($start) {
            echo $start;
        } else {
            echo "<span class='text-muted'>Not set</span>";
        }
        ?>
    </td>

    <td>
        <?php
        if ($row['status'] === 'approved') {
            echo '<span class="badge bg-success">Approved</span>';
        } elseif ($row['status'] === 'pending') {
            echo '<span class="badge bg-warning text-dark">Pending</span>';
        } elseif ($row['status'] === 'cancelled') {
            echo '<span class="badge bg-danger">Cancelled</span>';
        }
        ?>
    </td>

    <td class="text-nowrap">

        <?php if ($role === 'admin') { ?>

            <a href="./components/event/update.php?id=<?= $event_id ?>" class="btn btn-info btn-sm">
                <i class='bx bx-edit'></i>
            </a>

            <a href="./components/event/delete.php?id=<?= $event_id ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this event?');">
                <i class='bx bx-trash'></i>
            </a>

        <?php } ?>

        <?php if ($role === 'participant') { ?>

            <?php if ($isApproved) { ?>
                <span class="text-success fw-bold">Open to Participants</span>
            <?php } else { ?>
                <span class="text-muted">Not Available</span>
            <?php } ?>

        <?php } ?>

    </td>

</tr>

<?php } ?>

</tbody>
</table>
</div>

</div>

</section>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>