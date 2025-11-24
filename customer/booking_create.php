<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

requireLogin('customer');

$error = '';
$success = '';

// Get professional ID from URL
$professionalId = $_GET['professional'] ?? null;

// Get all professionals
$stmt = $conn->query("SELECT p.*, u.name FROM professionals p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.approved = TRUE 
                      ORDER BY u.name");
$professionals = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_SESSION['user_id'];
    $professionalId = clean($_POST['professional_id']);
    $serviceId = clean($_POST['service_id']);
    $date = clean($_POST['booking_date']);
    $time = clean($_POST['booking_time']);
    $notes = clean($_POST['notes']);
    
    $result = createBooking($customerId, $professionalId, $serviceId, $date, $time, $notes);
    
    if ($result['success']) {
        $success = 'Booking created successfully! Waiting for professional approval.';
        $_SESSION['success_message'] = $success;
        header('Location: bookings.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Create Booking';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="dashboard">
    <div class="container">
        <h1>Create New Booking</h1>
        <p style="color: var(--gray-color); margin-bottom: 2rem;">Book an appointment with your preferred professional</p>
        
        <div class="grid grid-2" style="align-items: start;">
            <div class="card">
                <div class="card-header">
                    <h3>Booking Details</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="bookingForm">
                        <div class="form-group">
                            <label class="form-label">Select Professional *</label>
                            <select name="professional_id" id="professional_id" class="form-control" required onchange="loadServices(this.value)">
                                <option value="">Choose a professional...</option>
                                <?php foreach ($professionals as $prof): ?>
                                    <option value="<?php echo $prof['id']; ?>" 
                                            <?php echo ($professionalId == $prof['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['name']); ?> - 
                                        <?php echo htmlspecialchars($prof['category']); ?> 
                                        (<?php echo htmlspecialchars($prof['location']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Select Service *</label>
                            <select name="service_id" id="service_id" class="form-control" required onchange="updateServiceInfo()">
                                <option value="">First select a professional...</option>
                            </select>
                            <div id="service_info" style="margin-top: 0.5rem;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Select Date *</label>
                            <input type="date" name="booking_date" id="booking_date" class="form-control" required 
                                   onchange="loadTimeSlots()">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Select Time *</label>
                            <div id="time-slots">
                                <p style="color: var(--gray-color);">Please select a professional, service, and date first</p>
                            </div>
                            <input type="hidden" name="booking_time" id="selected_time" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Additional Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="4" 
                                      placeholder="Any special requests or information..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Create Booking</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Booking Summary</h3>
                </div>
                <div class="card-body">
                    <div id="booking_summary">
                        <p style="text-align: center; color: var(--gray-color);">Fill in the booking details to see summary</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let servicesData = {};
let selectedService = null;

// Load services when professional is selected
function loadServices(professionalId) {
    if (!professionalId) {
        document.getElementById('service_id').innerHTML = '<option value="">First select a professional...</option>';
        return;
    }
    
    document.getElementById('service_id').innerHTML = '<option value="">Loading...</option>';
    
    fetch(`../api/get_slots.php?action=services&professional_id=${professionalId}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('service_id');
            select.innerHTML = '<option value="">Choose a service...</option>';
            
            if (data.success && data.services.length > 0) {
                data.services.forEach(service => {
                    servicesData[service.id] = service;
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - ₦${service.price} (${service.duration} mins)`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No services available</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('service_id').innerHTML = '<option value="">Error loading services</option>';
        });
}

// Update service info
function updateServiceInfo() {
    const serviceId = document.getElementById('service_id').value;
    const infoDiv = document.getElementById('service_info');
    
    if (serviceId && servicesData[serviceId]) {
        const service = servicesData[serviceId];
        selectedService = service;
        infoDiv.innerHTML = `
            <div style="padding: 0.75rem; background: var(--light-color); border-radius: 8px;">
                <strong>${service.name}</strong><br>
                <span style="color: var(--gray-color); font-size: 0.875rem;">
                    Price: ₦${service.price} • Duration: ${service.duration} minutes
                </span>
            </div>
        `;
        updateSummary();
    } else {
        infoDiv.innerHTML = '';
        selectedService = null;
    }
}

// Load time slots
function loadTimeSlots() {
    const professionalId = document.getElementById('professional_id').value;
    const serviceId = document.getElementById('service_id').value;
    const date = document.getElementById('booking_date').value;
    
    if (!professionalId || !serviceId || !date) return;
    
    loadTimeSlots(professionalId, serviceId, date);
    updateSummary();
}

// Update booking summary
function updateSummary() {
    const professionalSelect = document.getElementById('professional_id');
    const date = document.getElementById('booking_date').value;
    const time = document.getElementById('selected_time').value;
    
    const professionalName = professionalSelect.options[professionalSelect.selectedIndex].text;
    
    let html = '<div style="display: flex; flex-direction: column; gap: 1rem;">';
    
    if (professionalSelect.value) {
        html += `
            <div>
                <strong>Professional:</strong><br>
                <span style="color: var(--gray-color);">${professionalName.split(' - ')[0]}</span>
            </div>
        `;
    }
    
    if (selectedService) {
        html += `
            <div>
                <strong>Service:</strong><br>
                <span style="color: var(--gray-color);">${selectedService.name}</span>
            </div>
            <div>
                <strong>Price:</strong><br>
                <span style="color: var(--primary-color); font-size: 1.5rem; font-weight: bold;">₦${selectedService.price}</span>
            </div>
            <div>
                <strong>Duration:</strong><br>
                <span style="color: var(--gray-color);">${selectedService.duration} minutes</span>
            </div>
        `;
    }
    
    if (date) {
        html += `
            <div>
                <strong>Date:</strong><br>
                <span style="color: var(--gray-color);">${new Date(date).toLocaleDateString('en-US', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</span>
            </div>
        `;
    }
    
    if (time) {
        html += `
            <div>
                <strong>Time:</strong><br>
                <span style="color: var(--gray-color);">${time}</span>
            </div>
        `;
    }
    
    html += '</div>';
    
    document.getElementById('booking_summary').innerHTML = html;
}

// Auto-select professional if passed in URL
window.addEventListener('DOMContentLoaded', function() {
    const professionalId = document.getElementById('professional_id').value;
    if (professionalId) {
        loadServices(professionalId);
    }
});
</script>

<?php include '../includes/footer.php'; ?>