<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('customer');

$customerId = $_SESSION['user_id'];

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
$stmt->execute([$customerId]);
$totalBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status = 'pending'");
$stmt->execute([$customerId]);
$pendingBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status = 'approved'");
$stmt->execute([$customerId]);
$upcomingBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status = 'completed'");
$stmt->execute([$customerId]);
$completedBookings = $stmt->fetchColumn();

// Get recent bookings
$stmt = $conn->prepare("SELECT b.*, u.name as professional_name, p.category, p.location, s.name as service_name, s.price 
                        FROM bookings b 
                        JOIN professionals p ON b.professional_id = p.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.customer_id = ? 
                        ORDER BY b.created_at DESC 
                        LIMIT 5");
$stmt->execute([$customerId]);
$recentBookings = $stmt->fetchAll();

// Get notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$customerId]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Customer Dashboard';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p style="color: var(--gray-color);">Manage your bookings and explore new services</p>
        </div>
        
        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">📅</div>
                <div class="stat-content">
                    <h3><?php echo $totalBookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">⏳</div>
                <div class="stat-content">
                    <h3><?php echo $pendingBookings; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">✅</div>
                <div class="stat-content">
                    <h3><?php echo $upcomingBookings; ?></h3>
                    <p>Upcoming</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">🎉</div>
                <div class="stat-content">
                    <h3><?php echo $completedBookings; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="../public/professionals.php" class="btn btn-primary">Find Professionals</a>
                <a href="bookings.php" class="btn btn-outline">View All Bookings</a>
                <a href="profile.php" class="btn btn-outline">Update Profile</a>
            </div>
        </div>
        
        <div class="grid grid-2">
            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Bookings</h3>
                </div>
                <div class="card-body">
                    <?php if (count($recentBookings) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($recentBookings as $booking): ?>
                                <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <div>
                                            <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($booking['professional_name']); ?></h4>
                                            <p style="color: var(--gray-color); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($booking['service_name']); ?> • 
                                                <?php echo formatCurrency($booking['price']); ?>
                                            </p>
                                        </div>
                                        <span class="badge badge-<?php 
                                            echo match($booking['status']) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'cancelled' => 'danger',
                                                'completed' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <p style="font-size: 0.875rem;">
                                        📅 <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?> at 
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="bookings.php" class="btn btn-outline btn-sm">View All</a>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--gray-color);">No bookings yet. Start by finding professionals!</p>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="../public/professionals.php" class="btn btn-primary btn-sm">Find Professionals</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="card">
                <div class="card-header">
                    <h3>Notifications</h3>
                </div>
                <div class="card-body">
                    <?php if (count($notifications) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($notifications as $notif): ?>
                                <div style="padding: 1rem; background: <?php echo $notif['read_status'] ? 'white' : '#f0f9ff'; ?>; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                                    <p style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <small style="color: var(--gray-color);"><?php echo timeAgo($notif['created_at']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--gray-color);">No notifications</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>