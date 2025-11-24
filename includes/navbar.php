<?php
/**
 * Navigation Bar
 * Dynamic navigation based on user role and login status
 */
?>
<nav class="navbar">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">
            📅 BookingPro
        </a>
        
        <div class="menu-toggle" id="mobile-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <ul class="navbar-menu" id="navbar-menu">
            <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/public/professionals.php">Find Professionals</a></li>
            
            <?php if (isLoggedIn()): ?>
                
                <!-- Customer Menu -->
                <?php if (hasRole('customer')): ?>
                    <li><a href="<?php echo SITE_URL; ?>/customer/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/customer/bookings.php">My Bookings</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/customer/booking_create.php">Book Now</a></li>
                <?php endif; ?>
                
                <!-- Professional Menu -->
                <?php if (hasRole('professional')): ?>
                    <li><a href="<?php echo SITE_URL; ?>/professional/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/professional/bookings.php">Bookings</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/professional/manage_services.php">Services</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/professional/set_availability.php">Availability</a></li>
                <?php endif; ?>
                
                <!-- Admin Menu -->
                <?php if (hasRole('admin')): ?>
                    <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin Panel</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/manage_professionals.php">Professionals</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/manage_users.php">Users</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/manage_bookings.php">All Bookings</a></li>
                <?php endif; ?>
                
                <!-- User Info & Logout -->
                <li>
                    <span style="color: var(--gray-color); padding: 0.5rem 1rem;">
                        👤 Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                </li>
                <li><a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                
            <?php else: ?>
                <!-- Guest Menu -->
                <li><a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-outline btn-sm">Login</a></li>
                <li><a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary btn-sm">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('mobile-menu');
    const navbarMenu = document.getElementById('navbar-menu');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navbarMenu.classList.toggle('active');
            
            // Animate hamburger icon
            this.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.navbar')) {
                navbarMenu.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
        
        // Close menu when clicking a link
        navbarMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                navbarMenu.classList.remove('active');
                menuToggle.classList.remove('active');
            });
        });
    }
});
</script>