<link rel="stylesheet" href="/assets/css/bootstrap.min.css">

<div class="sidebar">
    <div class="logo-details">
        <i class='bx bxl-c-plus-plus'></i>
        <span class="logo_name">Enigma</span>
    </div>

    <ul class="nav-links">

        <li>
            <a href="/home.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
                <i class='bx bx-grid-alt'></i>
                <span class="links_name">Dashboard</span>
            </a>
        </li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
        <li>
            <a href="/user.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>">
                <i class='bx bx-box'></i>
                <span class="links_name">Users</span>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="/events.php">
                <i class='bx bx-list-ul'></i>
                <span class="links_name">Event list</span>
            </a>
        </li>

        <li>
            <a href="/participants.php">
                <i class='bx bx-pie-chart-alt-2'></i>
                <span class="links_name">Participants</span>
            </a>
        </li>

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
            <span class="dashboard">Dashboard</span>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search'></i>
        </div>

        <div class="profile-details">
            <img src="https://t4.ftcdn.net/jpg/00/97/00/09/360_F_97000908_wwH2goIihwrMoeV9QF3BW6HtpsVFaNVM.jpg">
            <span class="admin_name"><?php echo $_SESSION["Aname"] ?></span>
            <i class='bx bx-chevron-down'></i>
        </div>
    </nav>