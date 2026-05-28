<?php

define('SITE_NAME', 'BookingPro');
define('SITE_URL', 'http://localhost/bookme');
define('ADMIN_EMAIL', 'admin@bookingplatform.com');

define('APP_VERSION', '1.0.0');

define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/profiles/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/profiles/');

define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/gif',
    'image/webp'
]);

define('ITEMS_PER_PAGE', 12);
define('BOOKINGS_PER_PAGE', 10);
define('USERS_PER_PAGE', 20);

define('BOOKING_SLOT_INTERVAL', 30);
define('MIN_BOOKING_HOURS', 2);
define('MAX_BOOKING_DAYS', 90);
define('AUTO_COMPLETE_DAYS', 1);

define('CATEGORIES', [
    'Barber',
    'Tailor',
    'Mechanic',
    'Makeup Artist',
    'Photographer',
    'Plumber',
    'Electrician',
    'Caterer',
    'Event Planner',
    'Personal Trainer',
    'Hair Stylist',
    'Nail Technician',
    'Massage Therapist',
    'Tutor',
    'Cleaner',
    'DJ',
    'Chef',
    'Painter',
    'Carpenter',
    'Other'
]);

define('ENABLE_EMAIL', false);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@bookingplatform.com');
define('SMTP_FROM_NAME', 'BookingPro Platform');
define('SMTP_ENCRYPTION', 'tls');

define('EMAIL_BOOKING_CREATED_SUBJECT', 'Booking Request Received');
define('EMAIL_BOOKING_APPROVED_SUBJECT', 'Booking Approved!');
define('EMAIL_BOOKING_CANCELLED_SUBJECT', 'Booking Cancelled');

define('ENABLE_WHATSAPP', false);
define('WHATSAPP_API_KEY', 'your-api-key');
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/send');
define('WHATSAPP_PHONE_NUMBER', '+2348012345678');

date_default_timezone_set('Africa/Lagos');

define('DAYS_OF_WEEK', [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday'
]);

define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_APPROVED', 'approved');
define('BOOKING_STATUS_CANCELLED', 'cancelled');
define('BOOKING_STATUS_COMPLETED', 'completed');

define('ROLE_ADMIN', 'admin');
define('ROLE_PROFESSIONAL', 'professional');
define('ROLE_CUSTOMER', 'customer');

define('ENABLE_PAYMENTS', false);
define('PAYMENT_GATEWAY', 'paystack');
define('PAYMENT_PUBLIC_KEY', 'your-public-key');
define('PAYMENT_SECRET_KEY', 'your-secret-key');
define('PAYMENT_CURRENCY', 'NGN');

define('MIN_PASSWORD_LENGTH', 6);
define('SESSION_TIMEOUT', 7200);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15);

define('MIN_RATING', 1);
define('MAX_RATING', 5);
define('ENABLE_REVIEWS', true);

define('NOTIFICATIONS_PER_PAGE', 10);
define('AUTO_DELETE_OLD_NOTIFICATIONS', true);
define('NOTIFICATION_RETENTION_DAYS', 30);

define('SEARCH_RESULTS_PER_PAGE', 12);
define('ENABLE_SEARCH_SUGGESTIONS', true);

define('POPULAR_LOCATIONS', [
    'Trans Amadi',
    'Rumuola',
    'Eliozu',
    'GRA',
    'D-Line',
    'Rumukurushi',
    'Alakahia',
    'Choba',
    'Port Harcourt Township',
    'Rumuokoro',
    'Elelenwo',
    'Rumueme',
    'Old GRA',
    'New GRA',
    'Woji',
    'Rumueprikom',
    'Rumuokwuta',
    'Rumuigbo',
    'Rumuobiokani',
    'Mile 3',
    'Mile 4',
    'Oyigbo',
    'Other'
]);

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

define('ENABLE_CACHE', false);
define('CACHE_DURATION', 3600);

define('FACEBOOK_URL', '');
define('TWITTER_URL', '');
define('INSTAGRAM_URL', '');
define('LINKEDIN_URL', '');

// ==========================================
// MAINTENANCE MODE
// ==========================================

define('MAINTENANCE_MODE', false); // Set to true to enable maintenance mode
define('MAINTENANCE_MESSAGE', 'We are currently performing scheduled maintenance. Please check back soon!');

// ==========================================
// API SETTINGS (for mobile app integration)
// ==========================================

define('API_ENABLED', false);
define('API_KEY', 'your-api-key-here'); // ⚠️ UPDATE THIS
define('API_VERSION', 'v1');
?>