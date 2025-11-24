<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Authentication Functions
 */

// Register new user
function registerUser($name, $email, $password, $role, $phone = null) {
    global $conn;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Set status based on role
    $status = ($role === 'professional') ? 'pending' : 'active';
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $hashedPassword, $role, $phone, $status])) {
        $userId = $conn->lastInsertId();
        
        // If professional, create professional profile
        if ($role === 'professional') {
            $stmt = $conn->prepare("INSERT INTO professionals (user_id, category, bio, location, approved) VALUES (?, '', '', '', FALSE)");
            $stmt->execute([$userId]);
        }
        
        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

// Login user
function loginUser($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'suspended') {
            return ['success' => false, 'message' => 'Account suspended'];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Get professional_id if professional
        if ($user['role'] === 'professional') {
            $stmt = $conn->prepare("SELECT id FROM professionals WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $prof = $stmt->fetch();
            $_SESSION['professional_id'] = $prof['id'] ?? null;
        }
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Redirect if not logged in
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
    
    if ($role && !hasRole($role)) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

/**
 * Professional Management Functions
 */

// Add service
function addService($professionalId, $name, $price, $duration, $description = '') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO services (professional_id, name, price, duration, description) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$professionalId, $name, $price, $duration, $description]);
}

// Update service
function updateService($serviceId, $name, $price, $duration, $description = '') {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE services SET name = ?, price = ?, duration = ?, description = ? WHERE id = ?");
    return $stmt->execute([$name, $price, $duration, $description, $serviceId]);
}

// Delete service
function deleteService($serviceId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    return $stmt->execute([$serviceId]);
}

// Set availability
function setAvailability($professionalId, $day, $startTime, $endTime) {
    global $conn;
    
    // Check if already exists
    $stmt = $conn->prepare("SELECT id FROM availability WHERE professional_id = ? AND day = ?");
    $stmt->execute([$professionalId, $day]);
    
    if ($stmt->rowCount() > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE availability SET start_time = ?, end_time = ? WHERE professional_id = ? AND day = ?");
        return $stmt->execute([$startTime, $endTime, $professionalId, $day]);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO availability (professional_id, day, start_time, end_time) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$professionalId, $day, $startTime, $endTime]);
    }
}

// Get professional services
function getProfessionalServices($professionalId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM services WHERE professional_id = ? AND active = TRUE ORDER BY name");
    $stmt->execute([$professionalId]);
    return $stmt->fetchAll();
}

// Get professional availability
function getProfessionalAvailability($professionalId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM availability WHERE professional_id = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $stmt->execute([$professionalId]);
    return $stmt->fetchAll();
}

/**
 * Booking Management Functions
 */

// Create booking
function createBooking($customerId, $professionalId, $serviceId, $date, $time, $notes = '') {
    global $conn;
    
    // Check if slot is available
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE professional_id = ? AND booking_date = ? AND booking_time = ? AND status NOT IN ('cancelled')");
    $stmt->execute([$professionalId, $date, $time]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Time slot already booked'];
    }
    
    $stmt = $conn->prepare("INSERT INTO bookings (customer_id, professional_id, service_id, booking_date, booking_time, notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$customerId, $professionalId, $serviceId, $date, $time, $notes])) {
        $bookingId = $conn->lastInsertId();
        
        // Send notification
        sendBookingNotification($bookingId, 'created');
        
        return ['success' => true, 'booking_id' => $bookingId];
    }
    
    return ['success' => false, 'message' => 'Booking failed'];
}

// Get available time slots
function getAvailableSlots($professionalId, $serviceId, $date) {
    global $conn;
    
    // Get service duration
    $stmt = $conn->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    $duration = $service['duration'];
    
    // Get day of week
    $dayOfWeek = date('l', strtotime($date));
    
    // Get availability for that day
    $stmt = $conn->prepare("SELECT start_time, end_time FROM availability WHERE professional_id = ? AND day = ?");
    $stmt->execute([$professionalId, $dayOfWeek]);
    $availability = $stmt->fetch();
    
    if (!$availability) {
        return [];
    }
    
    // Get existing bookings for that date
    $stmt = $conn->prepare("SELECT booking_time, s.duration FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.professional_id = ? AND b.booking_date = ? AND b.status NOT IN ('cancelled')");
    $stmt->execute([$professionalId, $date]);
    $bookings = $stmt->fetchAll();
    
    // Generate time slots
    $slots = [];
    $start = strtotime($availability['start_time']);
    $end = strtotime($availability['end_time']);
    
    while ($start < $end) {
        $slotTime = date('H:i:s', $start);
        $slotEnd = $start + ($duration * 60);
        
        // Check if slot is available
        $isAvailable = true;
        foreach ($bookings as $booking) {
            $bookingStart = strtotime($booking['booking_time']);
            $bookingEnd = $bookingStart + ($booking['duration'] * 60);
            
            if (($start >= $bookingStart && $start < $bookingEnd) || 
                ($slotEnd > $bookingStart && $slotEnd <= $bookingEnd)) {
                $isAvailable = false;
                break;
            }
        }
        
        if ($isAvailable) {
            $slots[] = date('h:i A', $start);
        }
        
        $start += (BOOKING_SLOT_INTERVAL * 60);
    }
    
    return $slots;
}

// Approve booking
function approveBooking($bookingId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
    if ($stmt->execute([$bookingId])) {
        sendBookingNotification($bookingId, 'approved');
        return true;
    }
    return false;
}

// Cancel booking
function cancelBooking($bookingId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$bookingId])) {
        sendBookingNotification($bookingId, 'cancelled');
        return true;
    }
    return false;
}

// Complete booking
function completeBooking($bookingId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
    if ($stmt->execute([$bookingId])) {
        // Update professional total bookings
        $stmt = $conn->prepare("UPDATE professionals p JOIN bookings b ON p.id = b.professional_id SET p.total_bookings = p.total_bookings + 1 WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        return true;
    }
    return false;
}

/**
 * Utility Functions
 */

// Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format currency
function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

// Upload profile picture
function uploadProfilePicture($file, $userId) {
    $targetDir = UPLOAD_PATH;
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
    $targetFile = $targetDir . $newFileName;
    
    // Validate file
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Check if image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

// Send notification
function sendNotification($userId, $message, $type = 'info') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $message, $type]);
}

// Send booking notification
function sendBookingNotification($bookingId, $action) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT b.*, u1.name as customer_name, u1.email as customer_email, u1.phone as customer_phone, u2.name as professional_name, u2.email as professional_email, u2.phone as professional_phone, s.name as service_name FROM bookings b JOIN users u1 ON b.customer_id = u1.id JOIN professionals p ON b.professional_id = p.id JOIN users u2 ON p.user_id = u2.id JOIN services s ON b.service_id = s.id WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) return;
    
    $date = date('F j, Y', strtotime($booking['booking_date']));
    $time = date('h:i A', strtotime($booking['booking_time']));
    
    // Notifications based on action
    if ($action === 'created') {
        $customerMsg = "Your booking for {$booking['service_name']} on {$date} at {$time} has been submitted.";
        $professionalMsg = "New booking request from {$booking['customer_name']} for {$booking['service_name']} on {$date} at {$time}.";
        
        sendNotification($booking['customer_id'], $customerMsg, 'booking');
        sendNotification($booking['professional_id'], $professionalMsg, 'booking');
        
        // WhatsApp
        if (ENABLE_WHATSAPP) {
            sendWhatsApp($booking['professional_phone'], $professionalMsg);
        }
    } elseif ($action === 'approved') {
        $customerMsg = "Your booking for {$booking['service_name']} on {$date} at {$time} has been approved!";
        sendNotification($booking['customer_id'], $customerMsg, 'success');
        
        if (ENABLE_WHATSAPP) {
            sendWhatsApp($booking['customer_phone'], $customerMsg);
        }
    } elseif ($action === 'cancelled') {
        $customerMsg = "Your booking for {$booking['service_name']} on {$date} at {$time} has been cancelled.";
        $professionalMsg = "Booking from {$booking['customer_name']} for {$date} at {$time} has been cancelled.";
        
        sendNotification($booking['customer_id'], $customerMsg, 'warning');
        sendNotification($booking['professional_id'], $professionalMsg, 'warning');
    }
}

// Send WhatsApp message
function sendWhatsApp($phone, $message) {
    if (!ENABLE_WHATSAPP) return false;
    
    // Format phone number (remove + and spaces)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // WhatsApp API implementation (example)
    $data = [
        'phone' => $phone,
        'message' => $message,
        'api_key' => WHATSAPP_API_KEY
    ];
    
    // Send request (customize based on your WhatsApp API provider)
    $ch = curl_init(WHATSAPP_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Time ago function
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $time);
}
?>