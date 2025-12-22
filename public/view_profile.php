<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Get professional ID
$professionalId = $_GET['id'] ?? null;

if (!$professionalId) {
    header('Location: professionals.php');
    exit;
}

// Get professional details
$stmt = $conn->prepare("SELECT p.*, u.name, u.phone, u.email 
                        FROM professionals p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ? AND p.approved = TRUE");
$stmt->execute([$professionalId]);
$professional = $stmt->fetch();

if (!$professional) {
    header('Location: professionals.php');
    exit;
}

// Get services
$services = getProfessionalServices($professionalId);

// Get availability
$availability = getProfessionalAvailability($professionalId);

// Get recent reviews
$stmt = $conn->prepare("SELECT r.*, u.name as customer_name 
                        FROM reviews r 
                        JOIN users u ON r.customer_id = u.id 
                        WHERE r.professional_id = ? 
                        ORDER BY r.created_at DESC 
                        LIMIT 5");
$stmt->execute([$professionalId]);
$reviews = $stmt->fetchAll();

$pageTitle = $professional['name'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="profile-header-content">
                <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . $professional['profile_picture']; ?>" 
                     alt="<?php echo htmlspecialchars($professional['name']); ?>"
                     class="profile-avatar"
                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-avatar.png'">
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($professional['name']); ?></h1>
                    <p class="profile-category">
                        <?php echo htmlspecialchars($professional['category']); ?> • 
                        <?php echo htmlspecialchars($professional['location']); ?>
                    </p>
                    
                    <div class="profile-rating">
                        <?php if ($professional['rating'] > 0): ?>
                            <span style="color: #fbbf24; font-size: 1.25rem;">★</span>
                            <strong style="font-size: 1.25rem;"><?php echo number_format($professional['rating'], 1); ?></strong>
                            <span style="color: var(--gray-color);">(<?php echo $professional['total_bookings']; ?> bookings)</span>
                        <?php else: ?>
                            <span style="color: var(--gray-color);">New Professional</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="../customer/booking_create.php?professional=<?php echo $professional['id']; ?>" 
                           class="btn btn-primary">
                            📅 Book Now
                        </a>
                        <a href="tel:<?php echo $professional['phone']; ?>" class="btn btn-outline">
                            📞 Call
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Content -->
    <div class="container" style="padding: 2rem 20px;">
        <div class="grid grid-3" style="align-items: start;">
            <!-- Main Content -->
            <div style="grid-column: span 2;">
                <!-- About -->
                <div class="card">
                    <div class="card-header">
                        <h3>About</h3>
                    </div>
                    <div class="card-body">
                        <p style="line-height: 1.8; white-space: pre-line;">
                            <?php echo nl2br(htmlspecialchars($professional['bio'])); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Services -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Services & Pricing</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($services) > 0): ?>
                            <div class="service-list">
                                <?php foreach ($services as $service): ?>
                                    <div class="service-item">
                                        <div class="service-info">
                                            <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                            <?php if ($service['description']): ?>
                                                <p style="color: var(--gray-color); font-size: 0.875rem; margin-top: 0.25rem;">
                                                    <?php echo htmlspecialchars($service['description']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <p style="color: var(--gray-color); font-size: 0.875rem; margin-top: 0.5rem;">
                                                ⏱️ <?php echo $service['duration']; ?> minutes
                                            </p>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="service-price">
                                                <?php echo formatCurrency($service['price']); ?>
                                            </div>
                                            <a href="../customer/booking_create.php?professional=<?php echo $professional['id']; ?>&service=<?php echo $service['id']; ?>" 
                                               class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
                                                Book
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--gray-color);">No services listed yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Reviews -->
                <?php if (count($reviews) > 0): ?>
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3>Customer Reviews</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                            <div>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span style="color: <?php echo $i <= $review['rating'] ? '#fbbf24' : '#e5e7eb'; ?>;">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p style="color: var(--gray-color);">
                                            <?php echo htmlspecialchars($review['comment']); ?>
                                        </p>
                                        <small style="color: var(--gray-color);">
                                            <?php echo timeAgo($review['created_at']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Availability -->
                <div class="card">
                    <div class="card-header">
                        <h3>Availability</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($availability) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach ($availability as $avail): ?>
                                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                                        <strong><?php echo substr($avail['day'], 0, 3); ?></strong>
                                        <span style="color: var(--gray-color); font-size: 0.875rem;">
                                            <?php echo date('h:i A', strtotime($avail['start_time'])); ?> - 
                                            <?php echo date('h:i A', strtotime($avail['end_time'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--gray-color);">Schedule not set</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3>Contact</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <strong>📞 Phone</strong><br>
                                <a href="tel:<?php echo $professional['phone']; ?>" style="color: var(--primary-color);">
                                    <?php echo htmlspecialchars($professional['phone']); ?>
                                </a>
                            </div>
                            
                            <div>
                                <strong>📍 Location</strong><br>
                                <span style="color: var(--gray-color);">
                                    <?php echo htmlspecialchars($professional['location']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Book -->
                <div class="card" style="margin-top: 1.5rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <div class="card-body" style="text-align: center;">
                        <h3 style="color: white; margin-bottom: 1rem;">Ready to book?</h3>
                        <a href="../customer/booking_create.php?professional=<?php echo $professional['id']; ?>" 
                           class="btn" style="background: white; color: var(--primary-color); width: 100%;">
                            Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    min-height: 100vh;
}

.profile-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 3rem 0;
}

.profile-header-content {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.profile-info h1 {
    color: white;
    margin-bottom: 0.5rem;
}

.profile-category {
    font-size: 1.125rem;
    opacity: 0.95;
    margin-bottom: 1rem;
}

.profile-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.profile-actions {
    display: flex;
    gap: 1rem;
}

.review-item {
    padding: 1rem;
    background: var(--light-color);
    border-radius: 8px;
}

@media (max-width: 768px) {
    .profile-header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .grid-3 {
        grid-template-columns: 1fr !important;
    }
    
    .grid-3 > div:first-child {
        grid-column: span 1 !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>