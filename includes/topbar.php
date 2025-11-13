
 <?php
$currentUser = getCurrentUser();
$initials = '';
if ($currentUser) {
    $nameParts = explode(' ', $currentUser['full_name']);
    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
}
?>

<!-- Top Navbar -->
<nav class="topbar">
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="search-bar position-relative">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search employees, payroll...">
    </div>

    <div class="topbar-actions">
        <button class="notification-btn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge"></span>
        </button>
        
        <div class="user-profile dropdown">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div class="user-info">
                <h6><?php echo htmlspecialchars($currentUser['full_name']); ?></h6>
                <p><?php echo ucfirst($currentUser['role']); ?></p>
            </div>
            <i class="fas fa-chevron-down" style="color: var(--text-gray); font-size: 12px;"></i>
            
            <!-- Dropdown Menu -->
            <div class="dropdown-menu">
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    min-width: 200px;
    padding: 8px 0;
    z-index: 1000;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    color: var(--text-dark);
    text-decoration: none;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: var(--bg-light);
}

.dropdown-item i {
    width: 20px;
    font-size: 16px;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-divider {
    height: 1px;
    background: var(--primary-lightest);
    margin: 8px 0;
}
</style>