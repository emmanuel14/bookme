<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('customer');

$customerId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = clean($_POST['booking_id']);
    
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND customer_id = ?");
    $stmt->execute([$bookingId, $customerId]);
    
    if ($stmt->rowCount() > 0) {
        if (cancelBooking($bookingId)) {
            $_SESSION['success_message'] = 'Booking cancelled successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to cancel booking';
        }
    }
    header('Location: bookings.php');
    exit;
}

$status = isset($_GET['status']) ? clean($_GET['status']) : 'all';

$query = "SELECT b.*, 
          u.name as professional_name, 
          u.phone as professional_phone,
          p.category, 
          p.location,
          p.profile_picture,
          s.name as service_name, 
          s.price,
          s.duration
          FROM bookings b 
          JOIN professionals p ON b.professional_id = p.id 
          JOIN users u ON p.user_id = u.id 
          JOIN services s ON b.service_id = s.id 
          WHERE b.customer_id = ?";

$params = [$customerId];

if ($status !== 'all') {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Bookings';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Bookings</h1>
            <a href="booking_create.php" class="btn btn-primary">+ New Booking</a>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
                All Bookings
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="?status=approved" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">
                Upcoming
            </a>
            <a href="?status=completed" class="filter-tab <?php echo $status === 'completed' ? 'active' : ''; ?>">
                Completed
            </a>
            <a href="?status=cancelled" class="filter-tab <?php echo $status === 'cancelled' ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>
        
        <?php if (count($bookings) > 0): ?>
            <div class="bookings-grid">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="professional-info">
                                <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . $booking['profile_picture']; ?>" 
                                     alt="<?php echo htmlspecialchars($booking['professional_name']); ?>"
                                     class="booking-avatar"
                                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-avatar.png'">
                                <div>
                                    <h3><?php echo htmlspecialchars($booking['professional_name']); ?></h3>
                                    <p class="booking-category">
                                        <?php echo htmlspecialchars($booking['category']); ?> • 
                                        <?php echo htmlspecialchars($booking['location']); ?>
                                    </p>
                                </div>
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
                        
                        <div class="booking-body">
                            <div class="booking-detail">
                                <strong>Service:</strong>
                                <span><?php echo htmlspecialchars($booking['service_name']); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Date:</strong>
                                <span><i class="fas fa-calendar"></i> <?php echo date('l, F j, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Time:</strong>
                                <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Duration:</strong>
                                <span><i class="fas fa-hourglass-end"></i> <?php echo $booking['duration']; ?> minutes</span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Price:</strong>
                                <span class="booking-price"><?php echo formatCurrency($booking['price']); ?></span>
                            </div>
                            
                            <?php if ($booking['notes']): ?>
                                <div class="booking-notes">
                                    <strong>Notes:</strong>
                                    <p><?php echo htmlspecialchars($booking['notes']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="booking-contact">
                                <strong>Contact:</strong>
                                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['professional_phone']); ?></span>
                            </div>
                        </div>
                        
                        <div class="booking-footer">
                            <small style="color: var(--gray-color);">
                                Booked <?php echo timeAgo($booking['created_at']); ?>
                            </small>
                            
                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'approved'): ?>
                                <form method="POST" onsubmit="return confirmAction('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'completed'): ?>
                                <a href="#" class="btn btn-outline btn-sm">Leave Review</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No bookings found</h3>
                <p>
                    <?php if ($status === 'all'): ?>
                        You haven't made any bookings yet. Start by finding professionals in your area!
                    <?php else: ?>
                        You have no <?php echo $status; ?> bookings.
                    <?php endif; ?>
                </p>
                <a href="booking_create.php" class="btn btn-primary" style="margin-top: 1rem;">
                    Book Your First Appointment
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: 2px solid var(--border-color);
    background: white;
    color: var(--dark-color);
    font-weight: 600;
    transition: var(--transition);
}

.filter-tab:hover {
    border-color: var(--primary-color);
    background: var(--light-color);
}

.filter-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.bookings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 2rem;
}

.booking-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
}

.booking-card:hover {
    box-shadow: var(--shadow-lg);
}

.booking-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.professional-info {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.booking-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.booking-category {
    color: var(--gray-color);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.booking-body {
    padding: 1.5rem;
}

.booking-detail {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--light-color);
}

.booking-price {
    color: var(--primary-color);
    font-size: 1.25rem;
    font-weight: bold;
}

.booking-notes {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--light-color);
    border-radius: 8px;
}

.booking-notes p {
    margin-top: 0.5rem;
    color: var(--gray-color);
}

.booking-contact {
    margin-top: 1rem;
    padding: 0.75rem;
    background: #f0f9ff;
    border-radius: 8px;
}

.booking-footer {
    padding: 1rem 1.5rem;
    background: var(--light-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .bookings-grid {
        grid-template-columns: 1fr;
    }
    
    .booking-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}
</style>

<?php include '../includes/footer.php'; ?>