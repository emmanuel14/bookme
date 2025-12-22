<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('customer');

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = clean($_POST['name']);
        $phone = clean($_POST['phone']);
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $userId])) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $error = 'Failed to update profile';
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
            $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedPassword, $userId])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}

// Get booking statistics
$stmt = $conn->prepare("SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as upcoming
                        FROM bookings WHERE customer_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$pageTitle = 'My Profile';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <h1>My Profile</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="grid grid-2" style="align-items: start;">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-body">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar-large">
                            <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.png" 
                                 alt="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div>
                            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p style="color: var(--gray-color);">Customer</p>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>
                    
                    <form method="POST" style="margin-top: 2rem;">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="form-text">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('F Y', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <div>
                <!-- Account Statistics -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>Account Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['total']; ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['completed']; ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['upcoming']; ?></div>
                                <div class="stat-label">Upcoming</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="card">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="passwordForm">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                                <small class="form-text">Minimum <?php echo MIN_PASSWORD_LENGTH; ?> characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="bookings.php" class="btn btn-outline">View My Bookings</a>
                            <a href="booking_create.php" class="btn btn-outline">Create New Booking</a>
                            <a href="../public/professionals.php" class="btn btn-outline">Find Professionals</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar-section {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.profile-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--primary-color);
}

.profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: 8px;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray-color);
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.querySelector('input[name="new_password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
    }
    
    if (newPassword.length < <?php echo MIN_PASSWORD_LENGTH; ?>) {
        e.preventDefault();
        alert('Password must be at least <?php echo MIN_PASSWORD_LENGTH; ?> characters long!');
    }
});
</script>

<?php include '../includes/footer.php'; ?>