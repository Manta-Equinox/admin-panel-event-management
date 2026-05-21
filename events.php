<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

require_once __DIR__ . "/connect.php";

$role = $_SESSION['role'] ?? '';
$user_id = (int)($_SESSION['Aid'] ?? 0);


$assigned = [];
$registered = [];

if ($role === 'employee' && $user_id) {

    $res = mysqli_query($dbc, "
        SELECT event_id 
        FROM event_assignments
        WHERE staff_id = $user_id
    ");
    while ($r = mysqli_fetch_assoc($res)) {
        $assigned[] = (int)$r['event_id'];
    }

    $res = mysqli_query($dbc, "
        SELECT event_id 
        FROM event_participants
        WHERE participant_id = $user_id
    ");
    while ($r = mysqli_fetch_assoc($res)) {
        $registered[] = (int)$r['event_id'];
    }
}
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
$query1 = "SELECT 
                event_id,
                title,
                description,
                event_type,
                location,
                event_date,
                start_time,
                end_time,
                status
            FROM events";

$exe1 = mysqli_query($dbc, $query1);
?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
    <th>Event ID</th>
    <th>Event Name</th>
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

<?php while ($row1 = mysqli_fetch_assoc($exe1)) { ?>

<?php
$event_id = (int)$row1['event_id'];

$isAssigned = in_array($event_id, $assigned);
$isRegistered = in_array($event_id, $registered);

$isPrivate = strtolower($row1['event_type']) === 'private';
$isActive  = ($row1['status'] === 'approved');

$canJoin = $isActive && (!$isPrivate || $isAssigned);
?>

<tr>

    <td><?= $event_id ?></td>
    <td><?= $row1['title'] ?></td>
    <td><?= $row1['description'] ?></td>

    <td>
        <span class="badge bg-primary">
            <?= ucfirst($row1['event_type']) ?>
        </span>
    </td>

    <td><?= $row1['location'] ?></td>

    <td><?= $row1['event_date'] ?></td>

    <td>
        <?php
        $start = !empty($row1['start_time']) ? date("h:i A", strtotime($row1['start_time'])) : null;
        $end   = !empty($row1['end_time']) ? date("h:i A", strtotime($row1['end_time'])) : null;

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
        <?php if ($role === 'employee') { ?>

            <?php if ($row1['status'] === 'cancelled') { ?>
                <span class="badge bg-danger">Cancelled</span>

            <?php } elseif ($isRegistered) { ?>
                <span class="badge bg-success">Registered</span>

            <?php } elseif (!$isActive) { ?>
                <span class="badge bg-warning text-dark">Not Available</span>

            <?php } elseif ($isPrivate && $isAssigned) { ?>
                <span class="badge bg-warning text-dark">Invited</span>

            <?php } elseif (!$isPrivate) { ?>
                <span class="badge bg-primary">Public Event</span>

            <?php } else { ?>
                <span class="badge bg-secondary">Invite Only</span>
            <?php } ?>

        <?php } else { ?>

            <?php
            $status = $row1['status'];

            if ($status === 'approved') {
                echo '<span class="badge bg-success">Approved</span>';
            } elseif ($status === 'pending') {
                echo '<span class="badge bg-warning text-dark">Pending</span>';
            } elseif ($status === 'cancelled') {
                echo '<span class="badge bg-danger">Cancelled</span>';
            }
            ?>

        <?php } ?>
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

        <?php if ($role === 'employee') { ?>

            <?php if ($isRegistered) { ?>
                <button class="btn btn-success btn-sm" disabled>Joined</button>

            <?php } elseif ($canJoin) { ?>
                <a href="./components/event/participate.php?event_id=<?= $event_id ?>"
                   class="btn btn-primary btn-sm">
                    Participate
                </a>

            <?php } else { ?>
                <button class="btn btn-secondary btn-sm" disabled>
                    Invite Only
                </button>
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