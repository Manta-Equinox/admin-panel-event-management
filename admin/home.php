<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location:index.php');
    exit();
}

require_once __DIR__ . "/connect.php";

$q1 = "SELECT COUNT(*) AS e FROM events";
$q2 = "SELECT COUNT(*) AS p FROM event_participants";
$q3 = "SELECT COUNT(*) AS a FROM staff_users";
$q4 = "SELECT COUNT(*) AS l FROM attendance_logs";

$events = mysqli_fetch_assoc(mysqli_query($dbc, $q1));
$participants = mysqli_fetch_assoc(mysqli_query($dbc, $q2));
$staff = mysqli_fetch_assoc(mysqli_query($dbc, $q3));
$logs = mysqli_fetch_assoc(mysqli_query($dbc, $q4));

mysqli_close($dbc);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <?php include_once('./templates/sidebar.php'); ?>
</head>

<body>

<div class="home-content">
    <div class="overview-boxes">

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Events</div>
                <div class="number">
                    <?php echo $events['e']; ?>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Event Participants</div>
                <div class="number">
                    <?php echo $participants['p']; ?>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Staff Users</div>
                <div class="number">
                    <?php echo $staff['a']; ?>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Attendance Logs</div>
                <div class="number">
                    <?php echo $logs['l']; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>