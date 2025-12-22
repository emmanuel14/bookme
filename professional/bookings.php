<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('professional');

$professionalId = $_SESSION['professional_id'];
$success = '';
$error = '';

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookingId = clean($_POST['booking_id']);
    
    // Verify booking belongs to professional
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND professional_id = ?");
    $stmt->execute([$bookingId, $professionalId]);
    
    if ($stmt->rowCount() > 0) {
        if ($action === 'approve') {
            if (approveBooking($bookingId)) {
                $success = 'Booking approved successfully!';
            } else {
                $error = 'Failed to approve booking';
            }
        } elseif ($action === 'cancel') {
            if (cancelBooking($bookingId)) {
                $success = 'Booking cancelled';
            } else {
                $error = 'Failed to cancel booking';
            }
        } elseif ($action === 'complete') {
            if (completeBooking($bookingId)) {
                $success = 'Booking marked as completed!';
            } else {
                $error = 'Failed to complete booking';
            }
        }
    } else {
        $error = 'Booking not found';
    }
}

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$query = "SELECT b.*, 
          u.name as customer_name,
          u.email as customer_email,
          u.phone as customer_phone,
          s.name as service_name,
          s.price,
          s.duration
          FROM bookings b
          JOIN users u ON b.customer_id = u.id
          JOIN services s ON b.service_id = s.id
          WHERE b.professional_id = ?";

$params = [$professionalId];

if ($statusFilter !== 'all') {
    $query .= " AND b.status = ?";
    $params[] = $statusFilter;
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
            <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                All Bookings
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="?status=approved" class="filter-tab <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>">
                Upcoming
            </a>
            <a href="?status=completed" class="filter-tab <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">
                Completed
            </a>
            <a href="?status=cancelled" class="filter-tab <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>
        
        <?php if (count($bookings) > 0): ?>
            <div class="bookings-grid">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div>
                                <h3><?php echo htmlspecialchars($booking['customer_name']); ?></h3>
                                <p style="color: var(--gray-color); font-size: 0.875rem; margin-top: 0.25rem;">
                                    <?php echo htmlspecialchars($booking['service_name']); ?>
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
                        
                        <div class="booking-body">
                            <div class="booking-detail">
                                <strong>Date:</strong>
                                <span>📅 <?php echo date('l, F j, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Time:</strong>
                                <span>🕒 <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Duration:</strong>
                                <span>⏱️ <?php echo $booking['duration']; ?> minutes</span>
                            </div>
                            
                            <div class="booking-detail">
                                <strong>Price:</strong>
                                <span class="booking-price"><?php echo formatCurrency($booking['price']); ?></span>
                            </div>
                            
                            <div class="booking-contact">
                                <strong>Customer Contact:</strong><br>
                                📧 <?php echo htmlspecialchars($booking['customer_email']); ?><br>
                                📞 <?php echo htmlspecialchars($booking['customer_phone']); ?>
                            </div>
                            
                            <?php if ($booking['notes']): ?>
                                <div class="booking-notes">
                                    <strong>Customer Notes:</strong>
                                    <p><?php echo htmlspecialchars($booking['notes']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="booking-footer">
                            <small style="color: var(--gray-color);">
                                Booked <?php echo timeAgo($booking['created_at']); ?>
                            </small>
                            
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Decline this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            ✗ Decline
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($booking['status'] === 'approved'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            ✓ Mark Complete
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3>No bookings found</h3>
                <p>
                    <?php if ($statusFilter === 'all'): ?>
                        You don't have any bookings yet.
                    <?php else: ?>
                        No <?php echo $statusFilter; ?> bookings.
                    <?php endif; ?>
                </p>
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

.booking-contact {
    margin-top: 1rem;
    padding: 1rem;
    background: #f0f9ff;
    border-radius: 8px;
    font-size: 0.875rem;
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

.booking-footer {
    padding: 1rem 1.5rem;
    background: var(--light-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
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
}
</style>

<?php include '../includes/footer.php'; ?>