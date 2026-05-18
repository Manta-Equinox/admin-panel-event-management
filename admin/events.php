<?php
session_start();
if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

require_once "db_connect.php"; // ✅ FIX: use central DB connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Events</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $server ?? ''; ?>css/style.css">

    <?php include_once('./templates/sidebar.php'); ?>
</head>

<body>

<br><br>

<div class="row" style="margin-top: 75px; margin-bottom: 20px; margin-left: 20px;">
    <a href="./components/event/add.php">
        <button type="button" class="btn btn-primary ml-4 pl-2">Add New</button>
    </a>
</div>

<?php
$query1 = "SELECT * FROM events";
$exe1 = mysqli_query($conn, $query1);
?>

<table class="table container">
    <thead>
        <tr>
            <th>Event ID</th>
            <th>Event Name</th>
            <th>Description</th>
            <th>Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row1 = mysqli_fetch_assoc($exe1)) { ?>
            <tr>
                <td><?php echo $row1['eid']; ?></td>
                <td><?php echo $row1['name']; ?></td>
                <td><?php echo $row1['description']; ?></td>
                <td><?php echo $row1['type']; ?></td>
                <td><?php echo $row1['date']; ?></td>
                <td><?php echo $row1['time']; ?></td>
                <td>
                    <a href="./components/event/update.php?id=<?php echo $row1['eid']; ?>">
                        <button class="btn btn-info btn-sm">Edit</button>
                    </a>

                    <a href="./components/event/delete.php?id=<?php echo $row1['eid']; ?>">
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>