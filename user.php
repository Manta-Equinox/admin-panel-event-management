<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: home.php');
    exit();
}

require_once __DIR__ . "/connect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Users</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/home.css">
</head>

<body>

<?php include_once('./templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">
            <i class='bx bx-user'></i> Users
        </h4>

        <?php if ($_SESSION['role'] === 'admin') : ?>
            <a href="./components/user/add.php" class="btn btn-primary">
                <i class='bx bx-user-plus'></i> Add user
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped table-hover align-middle w-100">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Specialization</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

            <?php
            $query = "SELECT * FROM staff_users";
            $result = mysqli_query($dbc, $query);

            if (!$result) {
                die("Query Error: " . mysqli_error($dbc));
            }

            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

                    $id = $row['staff_id'];
                    $name = $row['name'];
                    $email = $row['email'];
                    $role = $row['role'];
                    $spec = $row['specialization'] ?? '-';

                    echo "
                    <tr>
                        <td>{$id}</td>
                        <td>{$name}</td>
                        <td>{$email}</td>
                        <td>{$role}</td>
                        <td>{$spec}</td>

                        <td>
                            <a href='./components/user/update.php?id={$id}' class='btn btn-info btn-sm'>
                                <i class='bx bx-edit'></i> Edit
                            </a>

                            <a href='./components/user/delete.php?id={$id}'
                               class='btn btn-danger btn-sm'
                               onclick=\"return confirm('Delete this user?');\">
                                <i class='bx bx-trash'></i> Delete
                            </a>
                        </td>
                    </tr>
                    ";
                }

            } else {
                echo "
                <tr>
                    <td colspan='6' class='text-center py-4'>
                        No users found
                    </td>
                </tr>";
            }
            ?>

            </tbody>
        </table>

    </div>
</div>

</section>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>