<?php
require_once __DIR__ . "/../../connect.php";
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
            text-align: center;
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

<div class="header">
    <h2>Public Events</h2>
    <p>Browse upcoming events</p>
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

        $title = htmlspecialchars($row['title']);
        $desc  = htmlspecialchars($row['description']);
        $date  = $row['event_date'] ?? 'TBA';
        $status = $row['status'] ?? 'open';

        echo "
        <div class='col-md-4'>
            <div class='card event-card p-3'>
                <h5>{$title}</h5>
                <p class='text-muted'>{$desc}</p>

                <p><strong>Date:</strong> {$date}</p>

                <span class='badge badge-status'>{$status}</span>
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