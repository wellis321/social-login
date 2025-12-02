<?php
/**
 * Security initialization and helper functions
 * Include this file at the top of all pages that need security features
 */

// Suppress errors during initialization
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Initialize secure session
try {
    initSecureSession();
} catch (Exception $e) {
    error_log("Session initialization error: " . $e->getMessage());
    // Fallback to basic session start
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}

// Set security headers
if (!headers_sent()) {
    @header('X-Content-Type-Options: nosniff');
    @header('X-Frame-Options: SAMEORIGIN');
    @header('X-XSS-Protection: 1; mode=block');
    @header('Referrer-Policy: strict-origin-when-cross-origin');
}
