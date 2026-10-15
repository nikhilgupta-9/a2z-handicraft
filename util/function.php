<?php
/**
 * Global Utility & Business Logic Functions
 * A2Z Handicraft
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure database connection is accessible
if (!isset($pdo)) {
    $pdo = getDB();
}

/* =========================================================================
   1. SECURITY & SANITIZATION
   ========================================================================= */

/**
 * Escape HTML output to prevent XSS
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input recursively
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return trim(strip_tags((string)$data));
}

/**
 * Read a scalar request value as a trimmed string.
 * Arrays (e.g. ?category[]=x) fall back to the default instead of throwing a TypeError.
 */
function request_string($source, $key, $default = '') {
    $value = $source[$key] ?? $default;
    return is_scalar($value) ? trim((string)$value) : $default;
}

/**
 * Generate or get CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render CSRF input field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate SEO-friendly slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check if IP/User is temporarily locked due to too many failed attempts
 * Maximum 5 failed attempts in 15 minutes
 */
function check_login_attempts($username) {
    global $pdo;
    $ip = get_client_ip();
    $cutoff = date('Y-m-d H:i:s', strtotime('-15 minutes'));

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (ip_address = ? OR username = ?) AND attempted_at >= ?");
    $stmt->execute([$ip, $username, $cutoff]);
    $attempts = (int)$stmt->fetchColumn();

    return $attempts < 5;
}

/**
 * Record a failed login attempt
 */
function record_login_attempt($username) {
    global $pdo;
    $ip = get_client_ip();
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
    $stmt->execute([$ip, $username]);
}

/**
 * Clear login attempts on successful login
 */
function clear_login_attempts($username) {
    global $pdo;
    $ip = get_client_ip();
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ? OR username = ?");
    $stmt->execute([$ip, $username]);
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_logged_in']);
}

/**
 * Require admin authentication for protected pages
 */
function require_admin_auth() {
    if (!is_admin_logged_in()) {
        set_flash('danger', 'Please login to access the admin panel.');
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Get currently authenticated admin user details
 */
function current_admin_user() {
    global $pdo;
    if (!is_admin_logged_in()) {
        return null;
    }

    static $currentUser = null;
    if ($currentUser === null) {
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, phone, role, avatar, status FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_user_id']]);
        $currentUser = $stmt->fetch();
    }
    return $currentUser;
}

/**
 * Audit log activity
 */
function log_activity($action, $details = '', $userId = null) {
    global $pdo;
    if ($userId === null && is_admin_logged_in()) {
        $userId = $_SESSION['admin_user_id'];
    }
    $ip = get_client_ip();
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (Exception $e) {
        error_log("Failed to write activity log: " . $e->getMessage());
    }
}

/* =========================================================================
   2. FLASH NOTIFICATIONS
   ========================================================================= */

function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function has_flash() {
    return !empty($_SESSION['flash_message']);
}

function get_flash() {
    if (!empty($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = e($flash['type']);
        $icon = ($type === 'success') ? 'fa-check-circle' : (($type === 'danger') ? 'fa-exclamation-triangle' : 'fa-info-circle');
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                    <i class="fa-solid ' . $icon . ' me-2"></i> ' . e($flash['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    return '';
}

/* =========================================================================
   3. SITE SETTINGS & GENERAL HELPERS
   ========================================================================= */

/**
 * Get all site settings as key-value array
 */
function get_all_settings() {
    global $pdo;
    static $settings = null;
    if ($settings === null) {
        $stmt = $pdo->query("SELECT key_name, value FROM site_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $settings;
}

/**
 * Get specific setting
 */
function get_setting($key, $default = '') {
    $settings = get_all_settings();
    return $settings[$key] ?? $default;
}

/**
 * Update or insert setting
 */
function update_setting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO site_settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
    return $stmt->execute([$key, $value]);
}

/**
 * Format currency price
 */
function format_price($price) {
    return '₹' . number_format((float)$price, 0);
}

/**
 * Truncate text nicely
 */
function truncate_text($text, $limit = 100) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '...';
}

/**
 * Resolve relative image URL to absolute or correct project URL
 */
function resolve_image_url($path, $fallback = 'images/dummy.png') {
    if (empty($path)) {
        return BASE_URL . '/' . $fallback;
    }
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    return BASE_URL . '/' . ltrim($path, '/');
}

/* =========================================================================
   4. FILE UPLOADS
   ========================================================================= */

/**
 * Securely upload image or file
 */
function upload_media($file, $subFolder = 'products', $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file parameters.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    // Max 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed 10MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    // Verify MIME type using finfo (except svg which is image/svg+xml or text/plain/xml)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'text/plain', 'image/x-icon', 'image/vnd.microsoft.icon'
    ];
    if (!in_array($mime, $allowedMimes) && $ext !== 'svg') {
        return ['success' => false, 'error' => 'Invalid file MIME type: ' . $mime];
    }

    $targetDir = UPLOADS_PATH . '/' . trim($subFolder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generate collision-resistant unique filename
    $filename = $subFolder . '-' . bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }

    $relativePath = 'uploads/' . trim($subFolder, '/') . '/' . $filename;
    return ['success' => true, 'path' => $relativePath, 'filename' => $filename];
}

/**
 * Delete uploaded file safely if inside uploads folder
 */
function delete_uploaded_file($path) {
    if (empty($path)) return false;
    // Don't delete base images/ files
    if (strpos($path, 'uploads/') !== 0) return false;

    $fullPath = ROOT_PATH . '/' . ltrim($path, '/');
    if (file_exists($fullPath) && is_file($fullPath)) {
        return @unlink($fullPath);
    }
    return false;
}

/* =========================================================================
   5. EMAIL MAILER (PHPMailer)
   ========================================================================= */

/**
 * Send email using PHPMailer or PHP mail()
 */
function send_app_mail($toEmail, $toName, $subject, $htmlBody, $plainBody = '', $replyToEmail = '', $replyToName = '') {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            $smtpHost = $_ENV['SMTP_HOST'] ?? '';
            $smtpUser = $_ENV['SMTP_USERNAME'] ?? '';
            $smtpPass = $_ENV['SMTP_PASSWORD'] ?? '';
            $smtpPort = (int)($_ENV['SMTP_PORT'] ?? 587);
            $smtpSecure = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
            $fromAddress = $_ENV['SMTP_FROM_ADDRESS'] ?? get_setting('email', 'a2zhandicraft@gmail.com');
            $fromName = $_ENV['SMTP_FROM_NAME'] ?? get_setting('site_name', 'A2Z Handicraft');

            if (!empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                $mail->SMTPSecure = $smtpSecure;
                $mail->Port       = $smtpPort;
            } else {
                $mail->isMail();
            }

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toEmail, $toName);
            if (!empty($replyToEmail) && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyToEmail, $replyToName);
            } else {
                $mail->addReplyTo($fromAddress, $fromName);
            }

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = !empty($plainBody) ? $plainBody : strip_tags($htmlBody);

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return ['success' => false, 'error' => $mail->ErrorInfo];
        }
    } else {
        // Fallback to native mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: A2Z Handicraft <" . get_setting('email', 'a2zhandicraft@gmail.com') . ">\r\n";
        $sent = @mail($toEmail, $subject, $htmlBody, $headers);
        return ['success' => $sent, 'error' => $sent ? '' : 'Native mail failed'];
    }
}

/**
 * Send notification email when lead is submitted
 */
function send_lead_notification($leadData) {
    $adminEmail = get_setting('email', 'a2zhandicraft@gmail.com');
    $subject = "New Inquiry on A2Z Handicraft from " . ($leadData['name'] ?? 'Website Visitor');
    
    $html = "<h2>New Website Lead Received</h2>";
    $html .= "<p><strong>Name:</strong> " . e($leadData['name']) . "</p>";
    $html .= "<p><strong>Email:</strong> " . e($leadData['email']) . "</p>";
    $html .= "<p><strong>Phone:</strong> " . e($leadData['phone']) . "</p>";
    $html .= "<p><strong>Subject:</strong> " . e($leadData['subject'] ?? 'Website Inquiry') . "</p>";
    $html .= "<p><strong>Message:</strong></p>";
    $html .= "<blockquote>" . nl2br(e($leadData['message'])) . "</blockquote>";
    $html .= "<p><small>Received at: " . date('Y-m-d H:i:s') . "</small></p>";

    // Reply-To is the visitor so the admin can answer the lead directly from the inbox
    return send_app_mail($adminEmail, 'A2Z Admin', $subject, $html, '', $leadData['email'] ?? '', $leadData['name'] ?? '');
}

/**
 * Validate, store and email a contact form submission.
 * Shared by contact-us.php (no-JS post) and form-process.php (AJAX).
 */
function process_contact_submission($post) {
    if (!verify_csrf_token($post['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Security session expired. Please refresh and try again.'];
    }

    $leadData = [
        'name' => clean_input(request_string($post, 'fname')),
        'email' => clean_input(request_string($post, 'email')),
        'phone' => clean_input(request_string($post, 'phone')),
        'subject' => clean_input(request_string($post, 'subject')) ?: 'Website Inquiry',
        'message' => clean_input(request_string($post, 'message'))
    ];

    if (empty($leadData['name']) || empty($leadData['email']) || empty($leadData['phone']) || empty($leadData['message'])) {
        return ['success' => false, 'message' => 'Please complete all required fields.'];
    }
    if (!filter_var($leadData['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (!create_lead($leadData)) {
        return ['success' => false, 'message' => 'Failed to submit inquiry. Please try again or reach us via WhatsApp directly.'];
    }

    // Lead is already saved in the admin panel, so a mail failure is only logged
    $mailResult = send_lead_notification($leadData);
    if (empty($mailResult['success'])) {
        error_log('Lead notification email failed: ' . ($mailResult['error'] ?? 'unknown error'));
    }

    return ['success' => true, 'message' => 'Thank you! Your message has been sent successfully. Our team will contact you shortly.'];
}

/* =========================================================================
   6. DATABASE QUERY HELPERS
   ========================================================================= */

function get_banners($activeOnly = true) {
    global $pdo;
    $sql = "SELECT * FROM banners";
    if ($activeOnly) {
        $sql .= " WHERE status = 1";
    }
    $sql .= " ORDER BY sort_order ASC, id ASC";
    return $pdo->query($sql)->fetchAll();
}

function get_categories($limit = null, $featuredOnly = false) {
    global $pdo;
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 1) AS product_count 
            FROM categories c WHERE c.status = 1";
    if ($featuredOnly) {
        $sql .= " AND c.is_featured = 1";
    }
    $sql .= " ORDER BY c.sort_order ASC, c.name ASC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $pdo->query($sql)->fetchAll();
}

function get_category_by_slug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND status = 1 LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_category_by_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_subcategories($categoryId = null, $activeOnly = true) {
    global $pdo;
    $sql = "SELECT s.*, c.name as category_name FROM subcategories s 
            LEFT JOIN categories c ON s.category_id = c.id WHERE 1=1";
    $params = [];
    if ($categoryId) {
        $sql .= " AND s.category_id = ?";
        $params[] = $categoryId;
    }
    if ($activeOnly) {
        $sql .= " AND s.status = 1";
    }
    $sql .= " ORDER BY s.sort_order ASC, s.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_subcategory_by_slug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM subcategories WHERE slug = ? AND status = 1 LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_products($filters = [], $limit = null, $offset = 0) {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
            s.name as subcategory_name, s.slug as subcategory_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN subcategories s ON p.subcategory_id = s.id 
            WHERE 1=1";
    $params = [];

    if (!isset($filters['include_inactive'])) {
        $sql .= " AND p.status = 1";
    }

    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['subcategory_id'])) {
        $sql .= " AND p.subcategory_id = ?";
        $params[] = $filters['subcategory_id'];
    }

    if (!empty($filters['is_featured'])) {
        $sql .= " AND p.is_featured = 1";
    }

    if (!empty($filters['is_bestseller'])) {
        $sql .= " AND p.is_bestseller = 1";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY p.id DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_products_count($filters = []) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM products p WHERE 1=1";
    $params = [];

    if (!isset($filters['include_inactive'])) {
        $sql .= " AND p.status = 1";
    }

    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['subcategory_id'])) {
        $sql .= " AND p.subcategory_id = ?";
        $params[] = $filters['subcategory_id'];
    }

    if (!empty($filters['is_featured'])) {
        $sql .= " AND p.is_featured = 1";
    }

    if (!empty($filters['is_bestseller'])) {
        $sql .= " AND p.is_bestseller = 1";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function get_product_by_slug($slug, $allowDraft = false) {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                   s.name as subcategory_name, s.slug as subcategory_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN subcategories s ON p.subcategory_id = s.id 
            WHERE p.slug = ?";
    if (!$allowDraft) {
        $sql .= " AND p.status = 1";
    }
    $sql .= " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_product_by_id($id, $allowDraft = false) {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                   s.name as subcategory_name, s.slug as subcategory_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN subcategories s ON p.subcategory_id = s.id 
            WHERE p.id = ?";
    if (!$allowDraft) {
        $sql .= " AND p.status = 1";
    }
    $sql .= " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

function get_related_products($categoryId, $excludeId = 0, $limit = 6) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 1 ORDER BY id DESC LIMIT ?");
    $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(2, $excludeId, PDO::PARAM_INT);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_testimonials($limit = null) {
    global $pdo;
    $sql = "SELECT * FROM testimonials WHERE status = 1 ORDER BY sort_order ASC, id ASC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $pdo->query($sql)->fetchAll();
}

function get_reviews($productId = null, $status = 'approved', $limit = null) {
    global $pdo;
    $sql = "SELECT r.*, p.name as product_name FROM reviews r 
            LEFT JOIN products p ON r.product_id = p.id WHERE 1=1";
    $params = [];
    if ($productId) {
        $sql .= " AND r.product_id = ?";
        $params[] = $productId;
    }
    if ($status) {
        $sql .= " AND r.status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY r.id DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_faqs($category = null, $limit = null) {
    global $pdo;
    $sql = "SELECT * FROM faqs WHERE status = 1";
    $params = [];
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    $sql .= " ORDER BY sort_order ASC, id ASC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_about_section() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM about_section LIMIT 1");
    $data = $stmt->fetch();
    if (!$data) {
        return [
            'title' => 'Discover Why Customers Love Our Handicraft Products Collection',
            'subtitle' => 'About Us',
            'image' => 'images/about.jpeg',
            'content_p1' => 'A2Z Handicraft is a trusted manufacturer and supplier of handcrafted decorative products.',
            'content_p2' => 'Our collection includes RDI Hangings, Torans, Dream Catchers and Flower Bunches.',
            'content_p3' => 'Consistent quality, wholesale pricing and timely delivery are our promises.',
            'experience_years' => '15+ Years',
            'happy_customers' => '10,000+',
            'products_count' => '500+',
            'craftsmen_count' => '50+'
        ];
    }
    return $data;
}

function create_lead($data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, subject, message, product_id, ip_address) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['subject'] ?? 'Website Inquiry',
        $data['message'],
        $data['product_id'] ?? null,
        get_client_ip()
    ]);
}

/* =========================================================================
   7. YOAST SEO META TAG GENERATOR
   ========================================================================= */

/**
 * Generate Yoast SEO style meta tags, OpenGraph, Twitter Cards, and Schema.org
 */
function render_seo_meta($custom = []) {
    $siteName = get_setting('site_name', 'A2Z Handicraft');
    $defaultTitle = get_setting('meta_title', $siteName . ' - Handmade Fashion, Home & Event Decor Manufacturer');
    $defaultDesc = get_setting('meta_description', 'Trusted manufacturer and supplier of handcrafted decorative products for homes, offices, hotels and events.');
    $defaultKeywords = get_setting('meta_keywords', 'handicraft, event decor, wedding decor, rdi hanging, fancy toran');
    $defaultImage = resolve_image_url(get_setting('site_logo', 'images/logo.png'));

    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $title = !empty($custom['title']) ? $custom['title'] : $defaultTitle;
    $description = !empty($custom['description']) ? $custom['description'] : $defaultDesc;
    $keywords = !empty($custom['keywords']) ? $custom['keywords'] : $defaultKeywords;
    $canonical = !empty($custom['canonical']) ? $custom['canonical'] : $currentUrl;
    $ogImage = !empty($custom['image']) ? resolve_image_url($custom['image']) : $defaultImage;
    $ogType = !empty($custom['type']) ? $custom['type'] : 'website';

    $output = [];
    $output[] = '<title>' . e($title) . '</title>';
    $output[] = '<meta name="description" content="' . e($description) . '">';
    $output[] = '<meta name="author" content="nikhilworks.com">';
    if (!empty($keywords)) {
        $output[] = '<meta name="keywords" content="' . e($keywords) . '">';
    }
    $output[] = '<link rel="canonical" href="' . e($canonical) . '">';

    // Open Graph / Facebook
    $output[] = '<meta property="og:locale" content="en_US">';
    $output[] = '<meta property="og:type" content="' . e($ogType) . '">';
    $output[] = '<meta property="og:title" content="' . e($title) . '">';
    $output[] = '<meta property="og:description" content="' . e($description) . '">';
    $output[] = '<meta property="og:url" content="' . e($canonical) . '">';
    $output[] = '<meta property="og:site_name" content="' . e($siteName) . '">';
    $output[] = '<meta property="og:image" content="' . e($ogImage) . '">';

    // Twitter Card
    $output[] = '<meta name="twitter:card" content="summary_large_image">';
    $output[] = '<meta name="twitter:title" content="' . e($title) . '">';
    $output[] = '<meta name="twitter:description" content="' . e($description) . '">';
    $output[] = '<meta name="twitter:image" content="' . e($ogImage) . '">';

    // Schema.org JSON-LD
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => $siteName,
        "url" => BASE_URL,
        "logo" => $defaultImage,
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => get_setting('phone', '+91-7983894672'),
            "contactType" => "customer service"
        ]
    ];

    if (!empty($custom['product'])) {
        $p = $custom['product'];
        $schema = [
            "@context" => "https://schema.org/",
            "@type" => "Product",
            "name" => $p['name'],
            "image" => resolve_image_url($p['featured_image']),
            "description" => truncate_text($p['description'], 160),
            "sku" => $p['sku'] ?? 'A2Z-' . $p['id'],
            "brand" => [
                "@type" => "Brand",
                "name" => $siteName
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => $canonical,
                "priceCurrency" => "INR",
                "price" => $p['price'],
                "availability" => ($p['in_stock'] ? "https://schema.org/InStock" : "https://schema.org/OutOfStock")
            ]
        ];
    }

    $output[] = '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';

    return implode("\n    ", $output);
}
