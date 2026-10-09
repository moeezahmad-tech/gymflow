<?php
/**
 * GymFlow - Database Configuration & PDO Connection
 */

// Simple robust .env parser
function loadEnv($filePath = null) {
    $possiblePaths = [];
    if ($filePath) {
        $possiblePaths[] = $filePath;
    }
    // 1. Parent directory (e.g. ../.env when app is inside /gymflow folder)
    $possiblePaths[] = dirname(__DIR__, 2) . '/.env';
    // 2. Current project root
    $possiblePaths[] = dirname(__DIR__) . '/.env';
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_readable($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                // Skip comments
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Strip quotes if present
                    $value = trim($value, '"\'');
                    if (!array_key_exists($key, $_ENV)) {
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        }
    }
}

// Load environment variables
loadEnv();

class Database {
    private static ?PDO $instance = null;

    /**
     * Get Singleton PDO connection
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $isProductionHost = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'techkreative.com') !== false;

            // Read env variables with GYMFLOW_ prefix support & smart defaults
            $rawHost = getenv('GYMFLOW_DB_HOST') ?: ($_ENV['GYMFLOW_DB_HOST'] ?? (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($isProductionHost ? 'sdb-65.hosting.stackcp.net' : '127.0.0.1'))));
            $dbName  = getenv('GYMFLOW_DB_NAME') ?: ($_ENV['GYMFLOW_DB_NAME'] ?? (getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($isProductionHost ? 'gymflow-35303339d352' : 'gym_flow'))));
            $dbUser  = getenv('GYMFLOW_DB_USERNAME') ?: ($_ENV['GYMFLOW_DB_USERNAME'] ?? (getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? ($isProductionHost ? 'gymflow-35303339d352' : 'root'))));
            $dbPass  = getenv('GYMFLOW_DB_PASSWORD') ?: ($_ENV['GYMFLOW_DB_PASSWORD'] ?? (getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? ($isProductionHost ? 'em3m:£(v_Znu' : '12345678'))));
            
            // Clean host in case protocol or port is passed (e.g., http://localhost:3306 or localhost:3306)
            $host = preg_replace('/^https?:\/\//i', '', $rawHost);
            $port = 3306;
            
            if (strpos($host, ':') !== false) {
                list($hostOnly, $extractedPort) = explode(':', $host, 2);
                $host = $hostOnly;
                $port = (int)$extractedPort ?: 3306;
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $dbUser, $dbPass, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                http_response_code(500);
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Database Setup Required</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="assets/css/style.css"></head><body style="background:#050507;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;"><div style="max-width:550px;background:#121217;border:1px solid #27272a;border-radius:16px;padding:32px;text-align:center;"><h2 style="color:#ef4444;margin-top:0;">Database Connection Notice</h2><p style="color:#a1a1aa;font-size:14px;line-height:1.6;">' . htmlspecialchars($e->getMessage()) . '</p><div style="background:#18181b;border:1px solid #3f3f46;border-radius:10px;padding:16px;margin:20px 0;text-align:left;font-size:12px;color:#d4d4d8;"><strong>Troubleshooting:</strong><br>1. Make sure MySQL database exists on your host.<br>2. Check that <code>.env</code> file is in the root or parent directory with valid <code>GYMFLOW_DB_*</code> credentials.<br>3. Import <code>schema.sql</code> into phpMyAdmin.</div><a href="." style="display:inline-block;background:#dc2626;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:bold;">Retry Connection</a></div></body></html>';
                exit;
            }
        }

        return self::$instance;
    }

    /**
     * Check if database connection is alive
     */
    public static function checkConnection(): bool {
        try {
            self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Global helper function to access PDO instance
 */
function getDB(): PDO {
    return Database::getConnection();
}
