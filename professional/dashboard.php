<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('professional');

$professionalId = $_SESSION['professional_id'];

$stmt = $conn->prepare("SELECT * FROM professionals WHERE id = ?");
$stmt->execute([$professionalId]);
$professional = $stmt->fetch();

if (empty($professional['category']) || empty($professional['bio'])) {
    header('Location: profile_setup.php');
    exit;
}

if (!$professional['approved']) {
    $pageTitle = 'Pending Approval';
    include '../includes/header.php';
    include '../includes/navbar.php';
    ?>
    <div class="container" style="padding: 4rem 20px; text-align: center;">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div style="font-size: 4rem; margin-bottom: 1rem;"><i class="fas fa-hourglass-end"></i></div>
            <h2>Account Pending Approval</h2>
            <p style="color: var(--gray-color); margin: 1rem 0;">
                Your professional account is currently under review. You will be notified once approved by our admin team.
            </p>
            <a href="../index.php" class="btn btn-primary">Back to Home</a>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE professional_id = ?");
$stmt->execute([$professionalId]);
$totalBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE professional_id = ? AND status = 'pending'");
$stmt->execute([$professionalId]);
$pendingBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE professional_id = ? AND booking_date >= CURDATE() AND status = 'approved'");
$stmt->execute([$professionalId]);
$upcomingBookings = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM services WHERE professional_id = ? AND active = TRUE");
$stmt->execute([$professionalId]);
$activeServices = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COALESCE(SUM(s.price), 0) as revenue 
                        FROM bookings b 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.professional_id = ? AND b.status = 'completed'");
$stmt->execute([$professionalId]);
$totalRevenue = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT b.*, u.name as customer_name, u.phone as customer_phone, s.name as service_name, s.price, s.duration 
                        FROM bookings b 
                        JOIN users u ON b.customer_id = u.id 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.professional_id = ? AND b.booking_date = CURDATE() AND b.status IN ('pending', 'approved')
                        ORDER BY b.booking_time");
$stmt->execute([$professionalId]);
$todayBookings = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT b.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email, s.name as service_name, s.price, s.duration 
                        FROM bookings b 
                        JOIN users u ON b.customer_id = u.id 
                        JOIN services s ON b.service_id = s.id 
                        WHERE b.professional_id = ? AND b.status = 'pending'
                        ORDER BY b.booking_date, b.booking_time 
                        LIMIT 5");
$stmt->execute([$professionalId]);
$pendingBookingsList = $stmt->fetchAll();

$pageTitle = 'Professional Dashboard';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p style="color: var(--gray-color);">Manage your bookings and services</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"><i class="fas fa-calendar"></i></div>
                <div class="stat-content">
                    <h3><?php echo $totalBookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-hourglass-end"></i></div>
                <div class="stat-content">
                    <h3><?php echo $pendingBookings; ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-check"></i></div>
                <div class="stat-content">
                    <h3><?php echo $upcomingBookings; ?></h3>
                    <p>Upcoming</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-money-bill"></i></div>
                <div class="stat-content">
                    <h3><?php echo formatCurrency($totalRevenue); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="add_service.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service</a>
                <a href="manage_services.php" class="btn btn-outline">Manage Services</a>
                <a href="set_availability.php" class="btn btn-outline">Set Availability</a>
                <a href="bookings.php" class="btn btn-outline">View All Bookings</a>
            </div>
        </div>
        
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    <h3>Today's Schedule</h3>
                    <p style="color: var(--gray-color); font-size: 0.875rem; font-weight: normal;">
                        <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (count($todayBookings) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($todayBookings as $booking): ?>
                                <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <div>
                                            <strong><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></strong>
                                            <span style="color: var(--gray-color);"> • <?php echo $booking['duration']; ?> mins</span>
                                        </div>
                                        <span class="badge badge-<?php echo $booking['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($booking['customer_name']); ?></h4>
                                    <p style="color: var(--gray-color); font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($booking['service_name']); ?> • 
                                        <?php echo formatCurrency($booking['price']); ?>
                                    </p>
                                    <p style="font-size: 0.875rem;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['customer_phone']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;"><i class="fas fa-inbox"></i></div>
                            <p style="color: var(--gray-color);">No bookings scheduled for today</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Pending Requests</h3>
                </div>
                <div class="card-body">
                    <?php if (count($pendingBookingsList) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($pendingBookingsList as $booking): ?>
                                <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: #fef3c7;">
                                    <div style="margin-bottom: 0.5rem;">
                                        <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($booking['customer_name']); ?></h4>
                                        <p style="color: var(--gray-color); font-size: 0.875rem;">
                                            <?php echo htmlspecialchars($booking['service_name']); ?> • 
                                            <?php echo formatCurrency($booking['price']); ?>
                                        </p>
                                    </div>
                                    <p style="font-size: 0.875rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?> at 
                                        <?php echo date('h:i A', strtotime($booking['booking_time'])); ?>
                                    </p>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <form method="POST" action="bookings.php" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form method="POST" action="bookings.php" style="display: inline;" onsubmit="return confirmAction('Decline this booking?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Decline</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($pendingBookings > 5): ?>
                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="bookings.php?status=pending" class="btn btn-outline btn-sm">View All Pending</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;"><i class="fas fa-check-circle"></i></div>
                            <p style="color: var(--gray-color);">No pending requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Services Summary -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Your Services</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>You have <strong><?php echo $activeServices; ?></strong> active service(s)</p>
                    <a href="manage_services.php" class="btn btn-outline btn-sm">Manage Services</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>