-- Create Database
CREATE DATABASE IF NOT EXISTS booking_platform;
USE booking_platform;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'professional', 'admin') NOT NULL,
    phone VARCHAR(20),
    status ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Professionals Table
CREATE TABLE professionals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    bio TEXT,
    location VARCHAR(255),
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    approved BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services Table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professional_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE
);

-- Availability Table
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professional_id INT NOT NULL,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_professional_day (professional_id, day)
);

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    professional_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Reviews Table (Bonus)d
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    professional_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE
);

-- Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Sample Admin User
INSERT INTO users (name, email, password, role, phone, status) VALUES
('Admin User', 'admin@bookingplatform.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+2348012345678', 'active');
-- Password: admin123

-- Insert Sample Professionals
INSERT INTO users (name, email, password, role, phone, status) VALUES
('John Barber', 'john.barber@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professional', '+2348023456789', 'active'),
('Sarah Tailor', 'sarah.tailor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professional', '+2348034567890', 'active'),
('Mike Mechanic', 'mike.mechanic@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professional', '+2348045678901', 'active'),
('Lisa Makeup', 'lisa.makeup@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professional', '+2348056789012', 'active'),
('David Photo', 'david.photo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professional', '+2348067890123', 'active');
-- Password: password123

-- Insert Professional Profiles
INSERT INTO professionals (user_id, category, bio, location, approved, rating, total_bookings) VALUES
(2, 'Barber', 'Professional barber with 10 years experience. Specializing in modern cuts and traditional shaves.', 'Port Harcourt, Trans Amadi', TRUE, 4.8, 45),
(3, 'Tailor', 'Expert tailor for all occasions. Custom designs and alterations. Quick turnaround time.', 'Port Harcourt, Rumuola', TRUE, 4.9, 38),
(4, 'Mechanic', 'Certified auto mechanic. All vehicle makes and models. Honest and reliable service.', 'Port Harcourt, Eliozu', TRUE, 4.7, 52),
(5, 'Makeup Artist', 'Professional makeup artist for weddings, events, and photoshoots. Premium products only.', 'Port Harcourt, GRA', TRUE, 5.0, 67),
(6, 'Photographer', 'Wedding and event photographer. High-quality photos and quick delivery.', 'Port Harcourt, D-Line', TRUE, 4.6, 29);

-- Insert Services
INSERT INTO services (professional_id, name, price, duration, description) VALUES
-- Barber services
(1, 'Haircut', 2000.00, 30, 'Professional haircut with styling'),
(1, 'Shave', 1500.00, 20, 'Clean shave with hot towel'),
(1, 'Haircut + Shave', 3000.00, 45, 'Complete grooming package'),
-- Tailor services
(2, 'Shirt Tailoring', 5000.00, 60, 'Custom shirt design and fitting'),
(2, 'Trouser Tailoring', 4000.00, 60, 'Custom trouser with perfect fit'),
(2, 'Full Suit', 25000.00, 120, 'Complete suit tailoring'),
-- Mechanic services
(3, 'Oil Change', 8000.00, 45, 'Full oil change service'),
(3, 'Brake Service', 15000.00, 90, 'Brake inspection and repair'),
(3, 'General Checkup', 5000.00, 60, 'Complete vehicle inspection'),
-- Makeup services
(4, 'Bridal Makeup', 35000.00, 120, 'Complete bridal makeup package'),
(4, 'Party Makeup', 15000.00, 60, 'Glamorous party makeup'),
(4, 'Natural Makeup', 10000.00, 45, 'Subtle everyday makeup'),
-- Photography services
(5, 'Wedding Photography', 150000.00, 480, 'Full day wedding coverage'),
(5, 'Portrait Session', 20000.00, 60, 'Professional portrait photoshoot'),
(5, 'Event Coverage', 50000.00, 240, 'Complete event photography');

-- Insert Availability (Monday to Friday, 9 AM - 5 PM for all)
INSERT INTO availability (professional_id, day, start_time, end_time) VALUES
-- Barber
(1, 'Monday', '09:00:00', '18:00:00'),
(1, 'Tuesday', '09:00:00', '18:00:00'),
(1, 'Wednesday', '09:00:00', '18:00:00'),
(1, 'Thursday', '09:00:00', '18:00:00'),
(1, 'Friday', '09:00:00', '18:00:00'),
(1, 'Saturday', '10:00:00', '16:00:00'),
-- Tailor
(2, 'Monday', '08:00:00', '17:00:00'),
(2, 'Tuesday', '08:00:00', '17:00:00'),
(2, 'Wednesday', '08:00:00', '17:00:00'),
(2, 'Thursday', '08:00:00', '17:00:00'),
(2, 'Friday', '08:00:00', '17:00:00'),
-- Mechanic
(3, 'Monday', '08:00:00', '18:00:00'),
(3, 'Tuesday', '08:00:00', '18:00:00'),
(3, 'Wednesday', '08:00:00', '18:00:00'),
(3, 'Thursday', '08:00:00', '18:00:00'),
(3, 'Friday', '08:00:00', '18:00:00'),
(3, 'Saturday', '09:00:00', '15:00:00'),
-- Makeup Artist
(4, 'Tuesday', '10:00:00', '19:00:00'),
(4, 'Wednesday', '10:00:00', '19:00:00'),
(4, 'Thursday', '10:00:00', '19:00:00'),
(4, 'Friday', '10:00:00', '19:00:00'),
(4, 'Saturday', '08:00:00', '20:00:00'),
(4, 'Sunday', '08:00:00', '20:00:00'),
-- Photographer
(5, 'Monday', '09:00:00', '17:00:00'),
(5, 'Wednesday', '09:00:00', '17:00:00'),
(5, 'Friday', '09:00:00', '17:00:00'),
(5, 'Saturday', '07:00:00', '22:00:00'),
(5, 'Sunday', '07:00:00', '22:00:00');

-- Insert Sample Customer
INSERT INTO users (name, email, password, role, phone, status) VALUES
('Jane Customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '+2348078901234', 'active');
-- Password: password123

-- Insert Sample Bookings
INSERT INTO bookings (customer_id, professional_id, service_id, booking_date, booking_time, status) VALUES
(7, 1, 1, '2025-11-25', '10:00:00', 'approved'),
(7, 4, 11, '2025-11-28', '14:00:00', 'pending'),
(7, 5, 14, '2025-12-01', '11:00:00', 'approved');

-- Create Indexes for Better Performance
CREATE INDEX idx_bookings_date ON bookings(booking_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_professionals_category ON professionals(category);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);