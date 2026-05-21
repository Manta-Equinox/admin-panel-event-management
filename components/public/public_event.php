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
            position: relative;
        }

        .back-btn {
            position: absolute;
            left: 15px;
            top: 15px;
            background: white;
            color: #5469d4;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn:hover {
            background: #eaeaea;
        }

        .event-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            transition: 0.2s ease;
            height: 100%;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .badge-status {
            background: #5469d4;
        }

        .btn-primary {
            background: #5469d4;
            border: none;
        }

        .btn-primary:hover {
            background: #3f51b5;
        }

        .btn-outline-primary {
            color: #5469d4;
            border-color: #5469d4;
        }

        .btn-outline-primary:hover {
            background: #5469d4;
            color: #fff;
        }

        .login-btn {
            background: #5469d4;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            text-align: center;
            display: block;
        }

        .login-btn:hover {
            background: #3f51b5;
            color: white;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="/index.php" class="back-btn">← Back</a>

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

        $id     = $row['event_id'];
        $title  = htmlspecialchars($row['title']);
        $desc   = htmlspecialchars($row['description']);
        $date   = $row['event_date'] ?? 'TBA';
        $status = $row['status'] ?? 'open';

        echo "
        <div class='col-md-4'>
            <div class='card event-card p-3 d-flex flex-column'>

                <h5>{$title}</h5>

                <p class='text-muted flex-grow-1'>{$desc}</p>

                <p><strong>Date:</strong> {$date}</p>

                <span class='badge badge-status mb-3'>{$status}</span>

                <div class='d-grid gap-2'>

                    <a href='check_event.php?id={$id}' class='btn btn-outline-primary btn-sm'>
                        View Details
                    </a>

                    <a href='/index.php' class='login-btn'>
                        Login to Participate
                    </a>

                </div>

            </div>
        </div>
        ";
    }

    echo '</div>';

} else {
    echo "
    <div class='text-center mt-5'>
        <h5>No public events available</h5>
        <a href='/index.php' class='btn btn-primary mt-3'>Go Back Home</a>
    </div>";
}
?>

</div>

</body>
</html>