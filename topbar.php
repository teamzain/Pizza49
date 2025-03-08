<?php
// Include the database connection
@include 'db/config.php';
// Include the notifications function
@include 'notification.php';

// Get the notifications
$notifications = getLowStockNotifications($conn);
$notificationCount = count($notifications);
?>

<div class="top-bar">
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    <div class="user-menu">
        <div class="notification-icon">
            <i class="fas fa-bell"></i>
            <?php if ($notificationCount > 0): ?>
                <span class="notification-count"><?php echo $notificationCount; ?></span>
            <?php endif; ?>
            <div class="notification-dropdown">
                <?php if ($notificationCount > 0): ?>
                    <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <li><?php echo htmlspecialchars($notification); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No new notifications</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="avatar-wrapper">
            <img src="img/logo.jpg" alt="User Avatar" class="user-avatar">
            
            <!-- Dropdown Menu -->
            <ul class="dropdown-menu">
                <li><a href="#">Update Username</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
.notification-icon {
    position: relative;
    cursor: pointer;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 5px;
    font-size: 12px;
}

.notification-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 10px;
    min-width: 200px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.notification-icon:hover .notification-dropdown {
    display: block;
}

.notification-dropdown ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.notification-dropdown li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.notification-dropdown li:last-child {
    border-bottom: none;
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: var(--secondary-color);
    border-bottom: 1px solid var(--border-color);
}

.left-section {
    display: flex;
    align-items: center;
}

.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: var(--primary-color);
    cursor: pointer;
    margin-right: 15px;
}

.search-bar {
    display: flex;
    align-items: center;
    background-color: var(--bg-color);
    border-radius: 8px;
    padding: 8px 15px;
    border: 1px solid var(--border-color);
}

.search-bar input {
    border: none;
    outline: none;
    padding: 5px;
    font-size: 14px;
    background: transparent;
    color: var(--text-color);
}

.user-menu {
    display: flex;
    align-items: center;
}

.notification-icon {
    position: relative;
    cursor: pointer;
    margin-right: 20px;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 5px;
    font-size: 12px;
}

.avatar-wrapper {
    position: relative;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
}

@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block;
    }

    .search-bar {
        display: none;
    }

    .top-bar {
        padding: 10px;
    }

    .user-menu {
        margin-left: auto;
    }
}
.avatar-wrapper {
position: relative;
display: inline-block;
}

.dropdown-menu {
display: none;
position: absolute;
top: 50px; /* Adjust this based on your design */
right: 0;
background-color: var(--secondary-color);
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
padding: 10px;
border-radius: 8px;
width: 150px;
z-index: 1000;
}

.dropdown-menu li {
list-style: none;
margin: 10px 0;
}

.dropdown-menu li a {
text-decoration: none;
color: var(--text-color);
font-size: 14px;
}

.dropdown-menu li a:hover {
color: var(--accent-color);
}

.avatar-wrapper:hover .dropdown-menu {
display: block;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%; /* Round shape */
    cursor: pointer;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.querySelector('.notification-icon');
    const notificationDropdown = document.querySelector('.notification-dropdown');

    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function() {
        notificationDropdown.style.display = 'none';
    });

    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>


