<?php
session_start();
if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

require_once __DIR__ . "/connect.php";
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

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
            <a href="./components/event/add.php" class="btn btn-primary">
                <i class='bx bx-plus'></i> Add New
            </a>
        <?php } ?>
    </div>

    <?php
    $query1 = "SELECT event_id, title, description, event_type, location, event_date, event_time FROM events";
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
        $event_id = intval($row1['event_id']);
        $user_id = $_SESSION['Aid'] ?? 0;
        $role = $_SESSION['role'] ?? '';

        $isAssigned = false;
        $isRegistered = false;

        if ($user_id && $role === 'employee') {

            $assignQuery = mysqli_query($dbc,
                "SELECT 1
                FROM event_assignments
                WHERE event_id = $event_id
                AND staff_id = $user_id
                LIMIT 1"
            );

            $isAssigned = mysqli_num_rows($assignQuery) > 0;

            $registerQuery = mysqli_query($dbc,
                "SELECT 1
                FROM event_participants
                WHERE event_id = $event_id
                AND participant_id = $user_id
                LIMIT 1"
            );

            $isRegistered = mysqli_num_rows($registerQuery) > 0;
        }


        $isPrivate = strtolower($row1['event_type']) === 'private';

        $canJoin = !$isPrivate || $isAssigned;
        ?>

        <tr>
            <td><?= $row1['event_id'] ?></td>
            <td><?= $row1['title'] ?></td>
            <td><?= $row1['description'] ?></td>

            <td>
                <span class="badge bg-primary">
                    <?= ucfirst($row1['event_type']) ?>
                </span>
            </td>

            <td><?= $row1['location'] ?></td>

            <td><?= $row1['event_date'] ?></td>

            <td><?= date("h:i A", strtotime($row1['event_time'])) ?></td>

            <td>
                <?php if ($_SESSION['role'] === 'employee') { ?>

                    <?php if ($isRegistered) { ?>
                        <span class="badge bg-success">
                            Registered
                        </span>

                    <?php } elseif ($isPrivate && $isAssigned) { ?>
                        <span class="badge bg-warning text-dark">
                            Invited
                        </span>

                    <?php } elseif (!$isPrivate) { ?>
                        <span class="badge bg-primary">
                            Public Event
                        </span>

                    <?php } else { ?>
                        <span class="badge bg-secondary">
                            Invite Only
                        </span>

                    <?php } ?>

                <?php } else { ?>

                    <span class="badge bg-info">
                        Admin View
                    </span>

                <?php } ?>
            </td>

            <td class="text-nowrap">

                <?php if ($_SESSION['role'] === 'admin') { ?>

                    <a href="./components/event/update.php?id=<?= $event_id ?>"
                    class="btn btn-info btn-sm">
                        <i class='bx bx-edit'></i>
                    </a>

                    <a href="./components/event/delete.php?id=<?= $event_id ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this event?');">
                        <i class='bx bx-trash'></i>
                    </a>

                <?php } ?>

                <?php if ($_SESSION['role'] === 'employee') { ?>

                    <?php if ($isRegistered) { ?>

                        <button class="btn btn-success btn-sm" disabled>
                            Joined
                        </button>

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