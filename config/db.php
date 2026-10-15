<?php
/**
 * Database Connection & Environment Config
 * A2Z Handicraft
 */

// Load Composer autoloader if present
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Load .env variables
$rootPath = dirname(__DIR__);
if (class_exists('Dotenv\Dotenv') && file_exists($rootPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($rootPath);
    $dotenv->safeLoad();
}

/**
 * Get PDO Database Connection (Singleton)
 *
 * @return PDO
 */
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'a2z_handicraft';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Log securely without exposing DB credentials
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please verify database server is running and .env is configured.");
        }
    }

    return $pdo;
}

// Global PDO instance for convenience
$pdo = getDB();
