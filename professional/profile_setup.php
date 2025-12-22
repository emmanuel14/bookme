<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('professional');

$professionalId = $_SESSION['professional_id'];
$error = '';
$success = '';

// Get current profile
$stmt = $conn->prepare("SELECT * FROM professionals WHERE id = ?");
$stmt->execute([$professionalId]);
$professional = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = clean($_POST['category']);
    $bio = clean($_POST['bio']);
    $location = clean($_POST['location']);
    
    // Validation
    if (empty($category) || empty($bio) || empty($location)) {
        $error = 'Please fill in all required fields';
    } else {
        $stmt = $conn->prepare("UPDATE professionals SET category = ?, bio = ?, location = ? WHERE id = ?");
        
        if ($stmt->execute([$category, $bio, $location, $professionalId])) {
            $success = 'Profile updated successfully!';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Failed to update profile';
        }
    }
}

$pageTitle = 'Complete Your Profile';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="card">
                <div class="card-header">
                    <h2>Complete Your Professional Profile</h2>
                    <p style="color: var(--gray-color); font-weight: normal; margin-top: 0.5rem;">
                        Tell customers about yourself and what you offer
                    </p>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Professional Category *</label>
                            <select name="category" class="form-control" required>
                                <option value="">Select your profession...</option>
                                <?php foreach (CATEGORIES as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                            <?php echo ($professional['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">What type of services do you provide?</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Bio / About You *</label>
                            <textarea name="bio" class="form-control" rows="6" required
                                      placeholder="Tell customers about your experience, expertise, and what makes your service special..."><?php echo htmlspecialchars($professional['bio']); ?></textarea>
                            <small class="form-text">Write a compelling description (minimum 50 characters)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Location *</label>
                            <select name="location" class="form-control" required>
                                <option value="">Select location...</option>
                                <?php foreach (POPULAR_LOCATIONS as $loc): ?>
                                    <option value="Port Harcourt, <?php echo $loc; ?>" 
                                            <?php echo ($professional['location'] === "Port Harcourt, $loc") ? 'selected' : ''; ?>>
                                        Port Harcourt, <?php echo $loc; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">Where do you provide your services?</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            Save Profile & Continue
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>💡 Profile Tips</h3>
                </div>
                <div class="card-body">
                    <ul style="line-height: 2;">
                        <li><strong>Be specific:</strong> Mention your experience, specializations, and certifications</li>
                        <li><strong>Be professional:</strong> Use clear, friendly language</li>
                        <li><strong>Highlight value:</strong> Explain what makes your service unique</li>
                        <li><strong>Build trust:</strong> Mention awards, training, or years in business</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>