<?php
/**
 * Application Constants
 * Global configuration settings for the booking platform
 */

// ==========================================
// SITE CONFIGURATION
// ==========================================

// Site Information
define('SITE_NAME', 'BookingPro');
define('SITE_URL', 'http://localhost/bookme'); // ⚠️ UPDATE THIS for production
define('ADMIN_EMAIL', 'admin@bookingplatform.com');

// Version
define('APP_VERSION', '1.0.0');

// ==========================================
// FILE UPLOAD SETTINGS
// ==========================================

// Upload Paths
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/profiles/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/profiles/');

// File Upload Limits
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes (5 * 1024 * 1024)
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/gif',
    'image/webp'
]);

// ==========================================
// PAGINATION SETTINGS
// ==========================================

define('ITEMS_PER_PAGE', 12);
define('BOOKINGS_PER_PAGE', 10);
define('USERS_PER_PAGE', 20);

// ==========================================
// BOOKING SETTINGS
// ==========================================

// Time slot interval in minutes (e.g., 30 = slots every 30 minutes)
define('BOOKING_SLOT_INTERVAL', 30);

// Minimum hours in advance required to make a booking
define('MIN_BOOKING_HOURS', 2);

// Maximum days in advance customers can book
define('MAX_BOOKING_DAYS', 90);

// Auto-complete bookings after how many days
define('AUTO_COMPLETE_DAYS', 1);

// ==========================================
// PROFESSIONAL CATEGORIES
// ==========================================

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

// ==========================================
// EMAIL CONFIGURATION (Optional)
// ==========================================

// Enable/Disable Email Notifications
define('ENABLE_EMAIL', false); // Set to true when configured

// SMTP Settings (for Gmail, Yahoo, etc.)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // ⚠️ UPDATE THIS
define('SMTP_PASSWORD', 'your-app-password');     // ⚠️ UPDATE THIS (use app password)
define('SMTP_FROM_EMAIL', 'noreply@bookingplatform.com');
define('SMTP_FROM_NAME', 'BookingPro Platform');
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Email Templates
define('EMAIL_BOOKING_CREATED_SUBJECT', 'Booking Request Received');
define('EMAIL_BOOKING_APPROVED_SUBJECT', 'Booking Approved!');
define('EMAIL_BOOKING_CANCELLED_SUBJECT', 'Booking Cancelled');

// ==========================================
// WHATSAPP CONFIGURATION (Optional)
// ==========================================

// Enable/Disable WhatsApp Notifications
define('ENABLE_WHATSAPP', false); // Set to true when configured

// WhatsApp API Settings (use services like Twilio, WhatsApp Business API, etc.)
define('WHATSAPP_API_KEY', 'your-api-key');           // ⚠️ UPDATE THIS
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/send'); // ⚠️ UPDATE THIS
define('WHATSAPP_PHONE_NUMBER', '+2348012345678');    // Your WhatsApp Business Number

// ==========================================
// TIMEZONE SETTINGS
// ==========================================

date_default_timezone_set('Africa/Lagos'); // ⚠️ UPDATE based on your location

// ==========================================
// DAYS OF WEEK
// ==========================================

define('DAYS_OF_WEEK', [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday'
]);

// ==========================================
// BOOKING STATUSES
// ==========================================

define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_APPROVED', 'approved');
define('BOOKING_STATUS_CANCELLED', 'cancelled');
define('BOOKING_STATUS_COMPLETED', 'completed');

// ==========================================
// USER ROLES
// ==========================================

define('ROLE_ADMIN', 'admin');
define('ROLE_PROFESSIONAL', 'professional');
define('ROLE_CUSTOMER', 'customer');

// ==========================================
// PAYMENT SETTINGS (Optional - for future)
// ==========================================

define('ENABLE_PAYMENTS', false); // Set to true when payment is integrated
define('PAYMENT_GATEWAY', 'paystack'); // 'paystack', 'flutterwave', 'stripe'
define('PAYMENT_PUBLIC_KEY', 'your-public-key');    // ⚠️ UPDATE THIS
define('PAYMENT_SECRET_KEY', 'your-secret-key');    // ⚠️ UPDATE THIS
define('PAYMENT_CURRENCY', 'NGN'); // NGN, USD, etc.

// ==========================================
// SECURITY SETTINGS
// ==========================================

// Password minimum length
define('MIN_PASSWORD_LENGTH', 6);

// Session timeout (in seconds) - 2 hours
define('SESSION_TIMEOUT', 7200);

// Maximum login attempts before lockout
define('MAX_LOGIN_ATTEMPTS', 5);

// Lockout duration in minutes
define('LOCKOUT_DURATION', 15);

// ==========================================
// RATING SETTINGS
// ==========================================

define('MIN_RATING', 1);
define('MAX_RATING', 5);
define('ENABLE_REVIEWS', true);

// ==========================================
// NOTIFICATION SETTINGS
// ==========================================

define('NOTIFICATIONS_PER_PAGE', 10);
define('AUTO_DELETE_OLD_NOTIFICATIONS', true);
define('NOTIFICATION_RETENTION_DAYS', 30);

// ==========================================
// SEARCH SETTINGS
// ==========================================

define('SEARCH_RESULTS_PER_PAGE', 12);
define('ENABLE_SEARCH_SUGGESTIONS', true);

// ==========================================
// LOCATION SETTINGS
// ==========================================

// Popular locations in Port Harcourt (Add more as needed)
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

// ==========================================
// DEBUG SETTINGS (DISABLE IN PRODUCTION!)
// ==========================================

define('DEBUG_MODE', true); // ⚠️ Set to FALSE in production!

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// ==========================================
// CACHE SETTINGS
// ==========================================

define('ENABLE_CACHE', false);
define('CACHE_DURATION', 3600); // 1 hour

// ==========================================
// SOCIAL MEDIA (Optional)
// ==========================================

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