<?php
/**
 * Security initialization and helper functions
 * Include this file at the top of all pages that need security features
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Initialize secure session
initSecureSession();

// Set security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
