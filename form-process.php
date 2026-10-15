<?php
/**
 * AJAX endpoint for the contact form (#contactForm in js/function.js).
 * Responds with plain "success" or an error message.
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request.');
}

$result = process_contact_submission($_POST);
echo $result['success'] ? 'success' : $result['message'];
