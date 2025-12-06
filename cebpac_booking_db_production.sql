-- =====================================================
-- Cebu Pacific Booking Tracker Database
-- Production Ready - For Hostinger Deployment
-- Includes travel_funds feature
-- =====================================================

-- Note: Do NOT include CREATE DATABASE in production
-- The database will be created via cPanel
-- Just use: USE your_database_name;

-- =====================================================
-- TABLE: accounts
-- Description: Stores CebuPac email accounts with travel funds
-- =====================================================
CREATE TABLE IF NOT EXISTS accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    travel_funds DECIMAL(12, 2) DEFAULT 0.00,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: bookings
-- Description: Stores flight bookings with passenger info
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
    itinerary_pdf VARCHAR(255),
    passengers_info TEXT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_account_id (account_id),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_departure_date (departure_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    a.travel_funds,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(b.total_price) as total_spent,
    a.registration_date
FROM accounts a
LEFT JOIN bookings b ON a.id = b.account_id
GROUP BY a.id, a.email, a.full_name, a.status, a.travel_funds, a.registration_date;

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
CREATE INDEX IF NOT EXISTS idx_accounts_status ON accounts(status);
CREATE INDEX IF NOT EXISTS idx_bookings_status ON bookings(status);
CREATE INDEX IF NOT EXISTS idx_bookings_date_range ON bookings(departure_date, return_date);
CREATE INDEX IF NOT EXISTS idx_bookings_account_status ON bookings(account_id, status);

-- =====================================================
-- Database Information
-- =====================================================
-- Tables Created:
-- 1. accounts - CebuPac email accounts (with travel_funds)
-- 2. bookings - Flight bookings (with passengers_info and itinerary_pdf)
--
-- Views Created:
-- 1. account_booking_summary - Summary of each account with booking stats
-- 2. upcoming_bookings - Bookings scheduled for the next 90 days
--
-- Features:
-- - Travel funds management
-- - Passenger information storage (JSON)
-- - PDF itinerary uploads
-- - Automatic status updates
-- =====================================================

