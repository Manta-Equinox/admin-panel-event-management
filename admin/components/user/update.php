<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}

$id = intval($_GET['id']);

if (!isset($dbc)) {
    die("Database connection failed.");
}

$stmt = $dbc->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$r = $result->fetch_assoc();

if (!$r) {
    die("Student not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_number = trim($_POST['student_number'] ?? '');
    $first_name     = trim($_POST['first_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $course         = trim($_POST['course'] ?? '');
    $year_level     = trim($_POST['year_level'] ?? '');
    $status         = trim($_POST['status'] ?? 'active');

    $update = $dbc->prepare("
        UPDATE students 
        SET student_number = ?,
            first_name = ?,
            last_name = ?,
            email = ?,
            course = ?,
            year_level = ?,
            status = ?
        WHERE student_id = ?
    ");

    $update->bind_param(
        "sssssssi",
        $student_number,
        $first_name,
        $last_name,
        $email,
        $course,
        $year_level,
        $status,
        $id
    );

    if ($update->execute()) {
        header("Location: ../../user.php");
        exit();
    } else {
        $fmsg = "Failed to update student: " . $update->error;
    }

    $update->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title>Enigma | Add Events</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="home.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<div class="sidebar">
    <div class="logo-details">
        <i class='bx bxl-c-plus-plus'></i>
        <span class="logo_name">Enigma</span>
    </div>

    <ul class="nav-links">
        <li><a href="home.php" class="active"><i class='bx bx-grid-alt'></i><span class="links_name">Dashboard</span></a></li>
        <li><a href="user.php"><i class='bx bx-box'></i><span class="links_name">Users</span></a></li>
        <li><a href="events.php"><i class='bx bx-list-ul'></i><span class="links_name">Event list</span></a></li>
        <li><a href="participants.php"><i class='bx bx-pie-chart-alt-2'></i><span class="links_name">Participants</span></a></li>
        <li><a href="#"><i class='bx bx-heart'></i><span class="links_name">Feedback</span></a></li>
        <li><a href="#"><i class='bx bx-cog'></i><span class="links_name">Queries</span></a></li>
        <li class="log_out"><a href="logout.php"><i class='bx bx-log-out'></i><span class="links_name">Log out</span></a></li>
    </ul>
</div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">Dashboard</span>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search'></i>
        </div>

        <div class="profile-details">
            <img src="https://t4.ftcdn.net/jpg/00/97/00/09/360_F_97000908_wwH2goIihwrMoeV9QF3BW6HtpsVFaNVM.jpg" alt="profile">

            <span class="admin_name">
                <?= htmlspecialchars($_SESSION["Aname"]) ?>
            </span>

            <i class='bx bx-chevron-down'></i>
        </div>
    </nav>

    <div class="container">

        <?php if (isset($fmsg)) { ?>
            <div class="alert alert-danger">
                <?= $fmsg ?>
            </div>
        <?php } ?>

        <h2 style="padding-top: 120px; margin-left: 20px">Update User</h2>

        <form method="post" style="margin-left: 20px">

            <div class="form-group">
                <label>Student Number</label>
                <input type="text" class="form-control" name="student_number"
                    value="<?= htmlspecialchars($r['student_number'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label>First Name</label>
                <input type="text" class="form-control" name="first_name"
                    value="<?= htmlspecialchars($r['first_name'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" class="form-control" name="last_name"
                    value="<?= htmlspecialchars($r['last_name'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label>E Mail</label>
                <input type="email" class="form-control" name="email"
                    value="<?= htmlspecialchars($r['email'] ?? '') ?>" required />
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <select class="form-control" name="year_level">
                    <option value="1" <?= ($r['year_level'] == 1 ? 'selected' : '') ?>>1</option>
                    <option value="2" <?= ($r['year_level'] == 2 ? 'selected' : '') ?>>2</option>
                    <option value="3" <?= ($r['year_level'] == 3 ? 'selected' : '') ?>>3</option>
                    <option value="4" <?= ($r['year_level'] == 4 ? 'selected' : '') ?>>4</option>
                </select>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select class="form-control" name="course">
                    <option value="BSINFO" <?= ($r['course'] == 'BSINFO' ? 'selected' : '') ?>>BSINFO</option>
                    <option value="BSINDU" <?= ($r['course'] == 'BSINDU' ? 'selected' : '') ?>>BSINDU</option>
                    <option value="BSED" <?= ($r['course'] == 'BSED' ? 'selected' : '') ?>>BSED</option>
                    <option value="BSOA" <?= ($r['course'] == 'BSOA' ? 'selected' : '') ?>>BSOA</option>
                    <option value="BSA" <?= ($r['course'] == 'BSA' ? 'selected' : '') ?>>BSA</option>
                    <option value="BSENTREP" <?= ($r['course'] == 'BSENTREP' ? 'selected' : '') ?>>BSENTREP</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="active" <?= ($r['status'] == 'active' ? 'selected' : '') ?>>Active</option>
                    <option value="inactive" <?= ($r['status'] == 'inactive' ? 'selected' : '') ?>>Inactive</option>
                </select>
            </div>

            <br><br>
            <input type="submit" class="btn btn-primary" value="Update Student">

        </form>
    </div>

    <?php require_once('../../templates/footer.php') ?>

</section>
</body>
</html>