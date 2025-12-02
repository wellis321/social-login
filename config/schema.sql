-- Social Media Login Training Simulator Database Schema
-- This file contains the complete database structure

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS social_login
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE social_login;

-- Users table - stores all practice accounts across all platforms
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(20) NOT NULL COMMENT 'twitter, facebook, or instagram',
    email VARCHAR(255) NOT NULL,
    username VARCHAR(100) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL COMMENT 'Password reset token',
    reset_token_expires TIMESTAMP NULL DEFAULT NULL COMMENT 'Token expiration time',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Allows soft deletion',
    UNIQUE KEY unique_email_platform (email, platform),
    INDEX idx_platform (platform),
    INDEX idx_email (email),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table - tracks user actions for training review
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    platform VARCHAR(20) NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'register, login, logout, delete_account, reset_account',
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_platform (platform),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account
-- Username: admin, Password: admin123
-- IMPORTANT: Change this password after first login!
INSERT INTO admin_users (username, password_hash, email)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com')
ON DUPLICATE KEY UPDATE username = username;

-- Sample test data (optional - for development/testing)
-- Uncomment the lines below if you want some sample accounts

-- INSERT INTO users (platform, email, username, password_hash, full_name, date_of_birth)
-- VALUES
-- ('twitter', 'test@example.com', 'testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '1990-01-01'),
-- ('facebook', 'test@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '1990-01-01'),
-- ('instagram', 'test@example.com', 'testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '1990-01-01');
