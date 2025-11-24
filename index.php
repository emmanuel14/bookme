<?php
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
include 'includes/header.php';
include 'includes/navbar.php';

// Get featured professionals
$stmt = $conn->query("SELECT p.*, u.name, u.phone FROM professionals p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.approved = TRUE 
                      ORDER BY p.rating DESC, p.total_bookings DESC 
                      LIMIT 6");
$featuredPros = $stmt->fetchAll();

// Get categories with count
$stmt = $conn->query("SELECT category, COUNT(*) as count FROM professionals 
                      WHERE approved = TRUE 
                      GROUP BY category 
                      ORDER BY count DESC");
$categories = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Find & Book Local Professionals</h1>
        <p>Connect with trusted barbers, tailors, mechanics, makeup artists, and more in your area</p>
        
        <div class="hero-search">
            <select id="search-category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach (CATEGORIES as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="search-location" class="form-control" placeholder="Enter location...">
            <button onclick="searchProfessionals()" class="btn btn-primary">Search</button>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 4rem 0; background: white;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">Popular Categories</h2>
        
        <div class="grid grid-4">
            <?php foreach (array_slice(CATEGORIES, 0, 8) as $category): ?>
                <a href="public/professionals.php?category=<?php echo urlencode($category); ?>" class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">
                        <?php
                        $icons = [
                            'Barber' => '💈',
                            'Tailor' => '✂️',
                            'Mechanic' => '🔧',
                            'Makeup Artist' => '💄',
                            'Photographer' => '📷',
                            'Plumber' => '🔨',
                            'Electrician' => '⚡',
                            'Caterer' => '🍽️'
                        ];
                        echo $icons[$category] ?? '👤';
                        ?>
                    </div>
                    <h4><?php echo $category; ?></h4>
                    <p style="color: var(--gray-color);">
                        <?php 
                        $catCount = array_filter($categories, fn($c) => $c['category'] === $category);
                        echo $catCount ? reset($catCount)['count'] : '0';
                        ?> professionals
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Professionals -->
<?php if (count($featuredPros) > 0): ?>
<section style="padding: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">Featured Professionals</h2>
        
        <div class="grid grid-3">
            <?php foreach ($featuredPros as $pro): ?>
                <div class="professional-card">
                    <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . $pro['profile_picture']; ?>" 
                         alt="<?php echo htmlspecialchars($pro['name']); ?>"
                         onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-avatar.png'">
                    
                    <div class="professional-card-body">
                        <h3 class="professional-card-title"><?php echo htmlspecialchars($pro['name']); ?></h3>
                        <p class="professional-card-category">
                            <?php echo htmlspecialchars($pro['category']); ?> • <?php echo htmlspecialchars($pro['location']); ?>
                        </p>
                        
                        <div class="profile-rating">
                            <span style="color: #fbbf24;">★</span>
                            <strong><?php echo number_format($pro['rating'], 1); ?></strong>
                            <span style="color: var(--gray-color);">(<?php echo $pro['total_bookings']; ?> bookings)</span>
                        </div>
                        
                        <div class="professional-card-footer">
                            <a href="public/view_profile.php?id=<?php echo $pro['id']; ?>" class="btn btn-primary btn-sm">View Profile</a>
                            <a href="customer/booking_create.php?professional=<?php echo $pro['id']; ?>" class="btn btn-outline btn-sm">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="public/professionals.php" class="btn btn-primary">View All Professionals</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- How It Works -->
<section style="padding: 4rem 0; background: white;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">How It Works</h2>
        
        <div class="grid grid-3">
            <div class="card" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h4>1. Search</h4>
                <p>Find professionals by category and location</p>
            </div>
            
            <div class="card" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <h4>2. Book</h4>
                <p>Select service and available time slot</p>
            </div>
            
            <div class="card" style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h4>3. Confirm</h4>
                <p>Get confirmation and enjoy the service</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section style="padding: 4rem 0; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-align: center;">
    <div class="container">
        <h2 style="color: white; margin-bottom: 1rem;">Are You a Professional?</h2>
        <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.95;">Join our platform and reach more customers</p>
        <a href="auth/register.php?role=professional" class="btn" style="background: white; color: var(--primary-color);">Register as Professional</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>