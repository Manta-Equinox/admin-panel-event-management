<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

require_once __DIR__ . "/../../connect.php";

if (!isset($_GET['event_id'])) {
    header("Location: ../../events.php");
    exit();
}

$event_id = intval($_GET['event_id']);
$user_id = intval($_SESSION['Aid']);
$role = $_SESSION['role'] ?? '';

$eventQuery = mysqli_query($dbc,
    "SELECT event_id, event_type
     FROM events
     WHERE event_id = $event_id
     LIMIT 1"
);

if (mysqli_num_rows($eventQuery) === 0) {
    header("Location: ../../events.php");
    exit();
}

$event = mysqli_fetch_assoc($eventQuery);
$isPrivate = strtolower($event['event_type']) === 'private';

if ($isPrivate) {

    $assignmentCheck = mysqli_query($dbc,
        "SELECT 1
         FROM event_assignments
         WHERE event_id = $event_id
         AND staff_id = $user_id
         LIMIT 1"
    );

    if (mysqli_num_rows($assignmentCheck) === 0) {

        echo "<script>
            alert('You are not invited to this private event.');
            window.location.href='../../events.php';
        </script>";
        exit();
    }
}

$check = mysqli_query($dbc,
    "SELECT 1
     FROM event_participants
     WHERE event_id = $event_id
     AND participant_id = $user_id
     LIMIT 1"
);

if (mysqli_num_rows($check) === 0) {

    mysqli_query($dbc,
        "INSERT INTO event_participants
        (event_id, participant_id, status)
        VALUES
        ($event_id, $user_id, 'registered')"
    );
}

header("Location: ../../events.php");
exit();
?>