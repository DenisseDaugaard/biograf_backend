<?php
require_once __DIR__ . "/vendor/autoload.php";

/**
 * 1. Load .env ONLY in local environment
 * Render sets env vars automatically, so .env is ignored there
 */
if (file_exists(__DIR__ . "/.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

/**
 * 2. Read environment variables
 * Render → Reads variables from dashboard
 * Local  → Reads variables from .env
 */
$host     = $_ENV["DB_HOST"]      ?? getenv("DB_HOST");
$dbname   = $_ENV["DB_NAME"]      ?? getenv("DB_NAME");
$user     = $_ENV["DB_USER"]      ?? getenv("DB_USER");
$password = $_ENV["DB_PASSWORD"]  ?? getenv("DB_PASSWORD");

try {
    /**
     * 3. Create PDO connection
     */
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $error) {
    die("❌ Database connection failed: " . $error->getMessage());
}
