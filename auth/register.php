<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = clean($_POST['role']);
    $phone = clean($_POST['phone']);
    
    // Validation
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($role, ['customer', 'professional'])) {
        $error = 'Invalid role selected';
    } else {
        $result = registerUser($name, $email, $password, $role, $phone);
        
        if ($result['success']) {
            if ($role === 'professional') {
                $success = 'Registration successful! Your account is pending approval. Please wait for admin approval.';
            } else {
                header("Location: login.php?registered=1");
                exit;
            }
        } else {
            $error = $result['message'];
        }
    }
}

$defaultRole = $_GET['role'] ?? 'customer';

$pageTitle = 'Register';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container" style="max-width: 600px; padding: 4rem 20px;">
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h2>Create Account</h2>
            <p style="color: var(--gray-color);">Join BookingPro today</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label class="form-label">I am a:</label>
                    <select name="role" class="form-control" required>
                        <option value="customer" <?php echo $defaultRole === 'customer' ? 'selected' : ''; ?>>
                            Customer (Looking for services)
                        </option>
                        <option value="professional" <?php echo $defaultRole === 'professional' ? 'selected' : ''; ?>>
                            Professional (Offering services)
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required 
                           placeholder="Enter your full name" value="<?php echo $_POST['name'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required 
                           placeholder="Enter your email" value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" required 
                           placeholder="+234 800 000 0000" value="<?php echo $_POST['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required 
                           placeholder="Minimum 6 characters">
                    <small class="form-text">Must be at least 6 characters long</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required 
                           placeholder="Re-enter password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>S
        </div>
        
        <div class="card-footer" style="text-align: center;">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (!validateForm('registerForm')) {
        e.preventDefault();
    }
});
</script>

<?php include '../includes/footer.php'; ?>