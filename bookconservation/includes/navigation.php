<?php
// includes/navigation.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <a href="index.php">
                <i class="bi bi-house-door"></i>
                <span>Arewa Conservation</span>
            </a>
        </div>
        
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="bi bi-house"></i> Home</a></li>
            <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>"><i class="bi bi-info-circle"></i> About</a></li>
            <li><a href="collections.php" class="<?php echo $current_page == 'collections.php' ? 'active' : ''; ?>"><i class="bi bi-archive"></i> Collections</a></li>
            <li><a href="digital-library.php" class="<?php echo $current_page == 'digital-library.php' ? 'active' : ''; ?>"><i class="bi bi-book"></i> Digital Library</a></li>
            <li><a href="research-portal.php" class="<?php echo $current_page == 'research-portal.php' ? 'active' : ''; ?>"><i class="bi bi-search"></i> Research</a></li>
            <li><a href="conservation-projects.php" class="<?php echo $current_page == 'conservation-projects.php' ? 'active' : ''; ?>"><i class="bi bi-tools"></i> Conservation</a></li>
            <li><a href="volunteer.php" class="<?php echo $current_page == 'volunteer.php' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Volunteer</a></li>
            <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>"><i class="bi bi-envelope"></i> Contact</a></li>
        </ul>
        
        <div class="user-menu">
            <div class="user-profile">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <img src="images/avatar-placeholder.jpg" alt="User" class="profile-pic">
                    <span><?php echo $_SESSION['user_name']; ?></span>
                    <i class="bi bi-chevron-down"></i>
                    <div class="profile-dropdown">
                        <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                        <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <img src="images/avatar-placeholder.jpg" alt="Guest" class="profile-pic">
                    <span>Guest</span>
                    <i class="bi bi-chevron-down"></i>
                    <div class="profile-dropdown">
                        <a href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <a href="register.php"><i class="bi bi-person-plus"></i> Register</a>
                        <a href="contact.php"><i class="bi bi-envelope"></i> Contact</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>