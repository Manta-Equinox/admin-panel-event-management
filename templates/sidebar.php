<link rel="stylesheet" href="/assets/css/bootstrap.min.css">

<?php
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['Aname'] ?? $_SESSION['Pname'] ?? 'Guest';
?>

<div class="sidebar">
    <div class="logo-details">
        <i class='bx bxl-c-plus-plus'></i>
        <span class="logo_name">Enigma</span>
    </div>

    <ul class="nav-links">

        <?php if ($role !== 'participant') { ?>
        <li>
            <a href="/home.php"
               class="<?= strpos($_SERVER['PHP_SELF'], 'home.php') !== false ? 'active' : '' ?>">
                <i class='bx bx-grid-alt'></i>
                <span class="links_name">Dashboard</span>
            </a>
        </li>
        <?php } ?>

        <?php if ($role === 'admin') { ?>
        <li>
            <a href="/user.php"
               class="<?= strpos($_SERVER['PHP_SELF'], 'user.php') !== false ? 'active' : '' ?>">
                <i class='bx bx-box'></i>
                <span class="links_name">Users</span>
            </a>
        </li>
        <?php } ?>

        <?php if ($role !== 'participant') { ?>
        <li>
            <a href="/events.php"
               class="<?= strpos($_SERVER['PHP_SELF'], 'events.php') !== false ? 'active' : '' ?>">
                <i class='bx bx-list-ul'></i>
                <span class="links_name">Event List</span>
            </a>
        </li>
        <?php } ?>

        <?php if ($role !== 'participant') { ?>
        <li>
            <a href="/ui/attendance/list.php"
               class="<?= strpos($_SERVER['PHP_SELF'], 'attendance') !== false ? 'active' : '' ?>">
                <i class='bx bx-time-five'></i>
                <span class="links_name">Attendance</span>
            </a>
        </li>
        <?php } ?>
        <li>
        <a href="/ui/attendance/attendance_scan.php">
            <i class='bx bx-qr-scan'></i>
            <span class="links_name">Scan QR</span>
        </a>
    </li>
        <?php if ($role === 'participant') { ?>
        <li>
            <a href="/ui/public/public_event.php"
               class="<?= strpos($_SERVER['PHP_SELF'], 'public_event.php') !== false ? 'active' : '' ?>">
                <i class='bx bx-calendar'></i>
                <span class="links_name">Events</span>
            </a>
        </li>
        <?php } ?>
        <li>
            <a href="/logout.php">
                <i class='bx bx-log-out'></i>
                <span class="links_name">Log out</span>
            </a>
        </li>

    </ul>
</div>

<section class="home-section">
    <nav>
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard">
                <?= ucfirst($role ?? 'User') ?>
            </span>
        </div>

        <div class="profile-details">
            <img src="https://t4.ftcdn.net/jpg/00/97/00/09/360_F_97000908_wwH2goIihwrMoeV9QF3BW6HtpsVFaNVM.jpg">
            <span class="admin_name"><?= htmlspecialchars($name) ?></span>
            <i class='bx bx-chevron-down'></i>
        </div>
    </nav>