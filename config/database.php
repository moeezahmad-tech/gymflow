<?php
/**
 * GymFlow - Database Configuration & PDO Connection
 */

// Simple robust .env parser
function loadEnv($filePath = null) {
    if (!$filePath) {
        $filePath = dirname(__DIR__) . '/.env';
    }
    
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

// Load environment variables
loadEnv();

class Database {
    private static ?PDO $instance = null;

    /**
     * Get Singleton PDO connection
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Read env variables with fallback defaults
            $rawHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1');
            $dbName  = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'gym_flow');
            $dbUser  = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root');
            $dbPass  = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');
            
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
                // Return clean error in development or log it
                error_log("Database Connection Error: " . $e->getMessage());
                die(json_encode([
                    'status' => 'error',
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                    'hint' => 'Please verify your database name, credentials, and MySQL server in .env'
                ]));
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
