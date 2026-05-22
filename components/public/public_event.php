<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname']) && !isset($_SESSION['participant_id'])) {
    header("Location: ../../index.php");
    exit();
}

$participant_id = $_SESSION['participant_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .header {
            background: #5469d4;
            color: white;
            padding: 20px;
        }

        .event-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            transition: 0.2s ease;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .badge-status {
            background: #5469d4;
        }
    </style>
</head>

<body>

<div class="header d-flex justify-content-between align-items-center px-4">
    <div>
        <h2 class="m-0">Public Events</h2>
        <p class="m-0">Browse upcoming events</p>
    </div>

    <a href="../../logout.php" class="btn btn-light btn-sm">
        Logout
    </a>
</div>

<div class="container mt-4">

<?php
$query = "SELECT * FROM events ORDER BY event_id DESC";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($dbc));
}

if (mysqli_num_rows($result) > 0) {

    echo '<div class="row g-3">';

    while ($row = mysqli_fetch_assoc($result)) {

        $event_id = $row['event_id'];
        $title = htmlspecialchars($row['title']);
        $desc  = htmlspecialchars($row['description']);
        $date  = $row['event_date'] ?? 'TBA';
        $status = $row['status'] ?? 'pending';
        $location = $row['location'] ?? 'Not specified';
        $capacity = $row['capacity'] ?? 'N/A';

        $joined = false;

        if ($participant_id) {
            $check = mysqli_prepare($dbc, "
                SELECT id FROM event_participants 
                WHERE event_id = ? AND participant_id = ?
            ");
            mysqli_stmt_bind_param($check, "ii", $event_id, $participant_id);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $joined = true;
            }
        }

        echo "
        <div class='col-md-4'>
            <div class='card event-card p-3 h-100'>

                <h5>{$title}</h5>
                <p class='text-muted'>{$desc}</p>

                <hr>

                <p><strong>Date:</strong> {$date}</p>
                <p><strong>Location:</strong> {$location}</p>
                <p><strong>Capacity:</strong> {$capacity}</p>

                <span class='badge badge-status mb-3'>{$status}</span>
        ";

        if ($status !== 'approved') {
            echo "<button class='btn btn-secondary w-100 mt-2' disabled>Not Available</button>";
        }

        else if ($joined) {
            echo "<button class='btn btn-success w-100 mt-2' disabled>Already Joined</button>";
        }

        else {
            echo "
                <form method='POST' action='participate.php'>
                    <input type='hidden' name='event_id' value='{$event_id}'>
                    <button type='submit' class='btn btn-primary w-100 mt-2'>
                        Participate
                    </button>
                </form>
            ";
        }

        echo "
            </div>
        </div>
        ";
    }

    echo '</div>';

} else {
    echo "<p class='text-center'>No public events available.</p>";
}
?>

</div>

</body>
</html>