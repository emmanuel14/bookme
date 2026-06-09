<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'slots';

if ($action === 'services') {
    // Get services for a professional
    $professionalId = $_GET['professional_id'] ?? null;
    
    if (!$professionalId) {
        echo json_encode(['success' => false, 'message' => 'Professional ID required']);
        exit;
    }
    
    $services = getProfessionalServices($professionalId);
    echo json_encode(['success' => true, 'services' => $services]);
    exit;
}

// Get available time slots
$professionalId = $_GET['professional_id'] ?? null;
$serviceId = $_GET['service_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$professionalId || !$serviceId || !$date) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$slots = getAvailableSlots($professionalId, $serviceId, $date);
echo json_encode(['success' => true, 'slots' => $slots]);
?>u