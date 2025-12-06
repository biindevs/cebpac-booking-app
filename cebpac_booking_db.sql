-- Cebu Pacific Booking Tracker Database
-- Created: November 23, 2025

-- Create Database
CREATE DATABASE IF NOT EXISTS cebpac_booking_db;
USE cebpac_booking_db;

-- =====================================================
-- TABLE: accounts
-- Description: Stores CebuPac email accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: bookings
-- Description: Stores flight bookings
-- =====================================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    booking_reference VARCHAR(50) NOT NULL UNIQUE,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    return_date DATE,
    number_of_passengers INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('confirmed', 'pending', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_account_id (account_id),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_departure_date (departure_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sample Data for Testing
-- =====================================================

-- Insert sample accounts
INSERT INTO accounts (email, full_name, phone, address, status) VALUES
('john.doe@example.com', 'John Doe', '+63 917 123 4567', '123 Cebu Street, Cebu City, Philippines', 'active'),
('jane.smith@example.com', 'Jane Smith', '+63 918 234 5678', '456 Manila Avenue, Manila, Philippines', 'active'),
('carlos.reyes@example.com', 'Carlos Reyes', '+63 919 345 6789', '789 Davao Road, Davao City, Philippines', 'inactive'),
('maria.santos@example.com', 'Maria Santos', '+63 920 456 7890', '321 Makati Boulevard, Makati, Philippines', 'active');

-- Insert sample bookings
INSERT INTO bookings (account_id, booking_reference, departure_city, arrival_city, departure_date, return_date, number_of_passengers, total_price, status, notes) VALUES
(1, 'BK001', 'Cebu (CEB)', 'Manila (MNL)', '2025-03-15', '2025-03-18', 2, 8500.00, 'completed', 'Flight completed. 2 adult passengers with standard baggage allowance.'),
(1, 'BK002', 'Cebu (CEB)', 'Davao (DVO)', '2025-04-05', NULL, 1, 3500.00, 'confirmed', 'One-way flight for business trip.'),
(1, 'BK003', 'Cebu (CEB)', 'Singapore (SIN)', '2025-05-22', '2025-05-29', 4, 18900.00, 'pending', 'Family vacation. Waiting for payment confirmation.'),
(1, 'BK004', 'Manila (MNL)', 'Cebu (CEB)', '2025-06-10', NULL, 3, 10500.00, 'cancelled', 'Cancelled due to schedule conflict.'),
(1, 'BK005', 'Cebu (CEB)', 'Manila (MNL)', '2025-07-18', NULL, 2, 7200.00, 'confirmed', 'Return flight to Manila for conference.'),
(2, 'BK006', 'Manila (MNL)', 'Cebu (CEB)', '2025-03-20', '2025-03-27', 3, 12750.00, 'completed', 'Family trip to Cebu.'),
(2, 'BK007', 'Cebu (CEB)', 'Iloilo (ILO)', '2025-04-12', NULL, 2, 5400.00, 'pending', 'Pending confirmation from airline.'),
(2, 'BK008', 'Cebu (CEB)', 'Davao (DVO)', '2025-05-05', NULL, 1, 3200.00, 'confirmed', 'Solo travel for work.'),
(2, 'BK009', 'Manila (MNL)', 'Palawan (PPS)', '2025-06-15', '2025-06-22', 4, 16800.00, 'confirmed', 'Summer vacation with family.'),
(2, 'BK010', 'Cebu (CEB)', 'Bangkok (BKK)', '2025-07-01', '2025-07-08', 2, 22400.00, 'confirmed', 'International flight to Thailand.'),
(3, 'BK011', 'Davao (DVO)', 'Manila (MNL)', '2025-03-25', NULL, 1, 4800.00, 'cancelled', 'Cancelled - account inactive.'),
(3, 'BK012', 'Davao (DVO)', 'Cebu (CEB)', '2025-04-30', NULL, 2, 6200.00, 'pending', 'Pending for inactive account.'),
(3, 'BK013', 'Manila (MNL)', 'Davao (DVO)', '2025-05-15', NULL, 3, 12600.00, 'pending', 'Pending confirmation.'),
(4, 'BK014', 'Makati (MNL)', 'Cebu (CEB)', '2025-03-10', '2025-03-17', 5, 21000.00, 'completed', 'Corporate team building trip.'),
(4, 'BK015', 'Cebu (CEB)', 'Singapore (SIN)', '2025-04-20', '2025-04-27', 2, 19600.00, 'confirmed', 'Honeymoon trip.'),
(4, 'BK016', 'Manila (MNL)', 'Hong Kong (HKG)', '2025-06-05', '2025-06-10', 1, 18500.00, 'pending', 'Business meeting in Hong Kong.'),
(4, 'BK017', 'Cebu (CEB)', 'Manila (MNL)', '2025-07-25', NULL, 3, 10500.00, 'confirmed', 'Family visit to Manila.'),
(4, 'BK018', 'Manila (MNL)', 'Davao (DVO)', '2025-08-01', '2025-08-05', 2, 9400.00, 'confirmed', 'Weekend getaway.'),
(4, 'BK019', 'Cebu (CEB)', 'Clark (CRK)', '2025-08-15', NULL, 1, 2800.00, 'pending', 'Business trip to Clark.'),
(4, 'BK020', 'Palawan (PPS)', 'Cebu (CEB)', '2025-09-10', NULL, 4, 12800.00, 'confirmed', 'Family return from Palawan.');

-- =====================================================
-- Create Views for Common Queries
-- =====================================================

-- View: Account Booking Summary
CREATE OR REPLACE VIEW account_booking_summary AS
SELECT 
    a.id,
    a.email,
    a.full_name,
    a.status,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(b.total_price) as total_spent,
    a.registration_date
FROM accounts a
LEFT JOIN bookings b ON a.id = b.account_id
GROUP BY a.id, a.email, a.full_name, a.status, a.registration_date;

-- View: Upcoming Bookings (Next 90 days)
CREATE OR REPLACE VIEW upcoming_bookings AS
SELECT 
    b.id,
    b.booking_reference,
    a.email,
    a.full_name,
    b.departure_city,
    b.arrival_city,
    b.departure_date,
    b.number_of_passengers,
    b.total_price,
    b.status,
    DATEDIFF(b.departure_date, CURDATE()) as days_until_departure
FROM bookings b
JOIN accounts a ON b.account_id = a.id
WHERE b.departure_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
  AND b.status = 'confirmed'
ORDER BY b.departure_date ASC;

-- =====================================================
-- Create Indexes for Performance
-- =====================================================
CREATE INDEX idx_accounts_status ON accounts(status);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_date_range ON bookings(departure_date, return_date);
CREATE INDEX idx_bookings_account_status ON bookings(account_id, status);

-- =====================================================
-- Database Information
-- =====================================================
-- Tables Created:
-- 1. accounts - CebuPac email accounts
-- 2. bookings - Flight bookings
--
-- Views Created:
-- 1. account_booking_summary - Summary of each account with booking stats
-- 2. upcoming_bookings - Bookings scheduled for the next 90 days
--
-- Sample Data:
-- - 4 accounts
-- - 20 bookings with various statuses
-- =====================================================
