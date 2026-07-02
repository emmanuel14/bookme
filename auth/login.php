<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    header("Location: " . SITE_URL . "/$role/dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    $result = loginUser($email, $password);
    j
    if ($result['success']) {
        header("Location: " . SITE_URL . "/{$result['role']}/dashboard.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container" style="max-width: 500px; padding: 4rem 20px;">
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h2>Welcome Back</h2>
            <p style="color: var(--gray-color);">Login to your account</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Registration successful! Please login.</div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required 
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
        
        <div class="card-footer" style="text-align: center;">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>