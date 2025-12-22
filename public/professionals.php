<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Get filters
$categoryFilter = $_GET['category'] ?? '';
$locationFilter = $_GET['location'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Build query
$query = "SELECT p.*, u.name, u.phone 
          FROM professionals p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.approved = TRUE";

$params = [];

if ($categoryFilter) {
    $query .= " AND p.category = ?";
    $params[] = $categoryFilter;
}

if ($locationFilter) {
    $query .= " AND p.location LIKE ?";
    $params[] = "%$locationFilter%";
}

if ($searchQuery) {
    $query .= " AND (u.name LIKE ? OR p.bio LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Get total count
$countStmt = $conn->prepare(str_replace("p.*, u.name, u.phone", "COUNT(*) as total", $query));
$countStmt->execute($params);
$totalProfessionals = $countStmt->fetch()['total'];
$totalPages = ceil($totalProfessionals / ITEMS_PER_PAGE);

// Add ordering and pagination
$query .= " ORDER BY p.rating DESC, p.total_bookings DESC LIMIT ? OFFSET ?";
$params[] = ITEMS_PER_PAGE;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->execute($params);
$professionals = $stmt->fetchAll();

$pageTitle = 'Find Professionals';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div style="background: var(--light-color); min-height: 100vh;">
    <!-- Search Header -->
    <div style="background: white; padding: 2rem 0; box-shadow: var(--shadow);">
        <div class="container">
            <h1 style="margin-bottom: 1.5rem;">Find Local Professionals.</h1>
            
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by name or keywords..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>"
                       style="flex: 1; min-width: 200px;">
                
                <select name="category" class="form-control" style="min-width: 180px;">
                    <option value="">All Categories</option>
                    <?php foreach (CATEGORIES as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="location" class="form-control" style="min-width: 180px;">
                    <option value="">All Locations</option>
                    <?php foreach (POPULAR_LOCATIONS as $loc): ?>
                        <option value="<?php echo $loc; ?>" <?php echo $locationFilter === $loc ? 'selected' : ''; ?>>
                            <?php echo $loc; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">Search</button>
                
                <?php if ($categoryFilter || $locationFilter || $searchQuery): ?>
                    <a href="professionals.php" class="btn btn-outline">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <p style="color: var(--gray-color); margin-top: 1rem;">
                Found <?php echo $totalProfessionals; ?> professional(s)
            </p>
        </div>
    </div>
    
    <!-- Professionals Grid -->
    <div class="container" style="padding: 2rem 20px;">
        <?php if (count($professionals) > 0): ?>
            <div class="grid grid-3">
                <?php foreach ($professionals as $pro): ?>
                    <div class="professional-card">
                        <img src="<?php echo SITE_URL . '/assets/uploads/profiles/' . $pro['profile_picture']; ?>" 
                             alt="<?php echo htmlspecialchars($pro['name']); ?>"
                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-avatar.png'">
                        
                        <div class="professional-card-body">
                            <h3 class="professional-card-title"><?php echo htmlspecialchars($pro['name']); ?></h3>
                            <p class="professional-card-category">
                                <?php echo htmlspecialchars($pro['category']); ?><br>
                                <small>📍 <?php echo htmlspecialchars($pro['location']); ?></small>
                            </p>
                            
                            <div class="profile-rating" style="margin: 1rem 0;">
                                <?php if ($pro['rating'] > 0): ?>
                                    <span style="color: #fbbf24;">★</span>
                                    <strong><?php echo number_format($pro['rating'], 1); ?></strong>
                                    <span style="color: var(--gray-color); font-size: 0.875rem;">
                                        (<?php echo $pro['total_bookings']; ?> bookings)
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray-color); font-size: 0.875rem;">New Professional</span>
                                <?php endif; ?>
                            </div>
                            
                            <p style="color: var(--gray-color); font-size: 0.875rem; margin-bottom: 1rem;">
                                <?php echo substr(htmlspecialchars($pro['bio']), 0, 100); ?>...
                            </p>
                            
                            <div class="professional-card-footer">
                                <a href="view_profile.php?id=<?php echo $pro['id']; ?>" class="btn btn-outline btn-sm">
                                    View Profile
                                </a>
                                <a href="../customer/booking_create.php?professional=<?php echo $pro['id']; ?>" class="btn btn-primary btn-sm">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo $categoryFilter; ?>&location=<?php echo $locationFilter; ?>&search=<?php echo $searchQuery; ?>">
                            ← Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&category=<?php echo $categoryFilter; ?>&location=<?php echo $locationFilter; ?>&search=<?php echo $searchQuery; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo $categoryFilter; ?>&location=<?php echo $locationFilter; ?>&search=<?php echo $searchQuery; ?>">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>No professionals found</h3>
                <p>Try adjusting your search filters</p>
                <a href="professionals.php" class="btn btn-primary" style="margin-top: 1rem;">
                    View All Professionals
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
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
</style>

<?php include '../includes/footer.php'; ?>