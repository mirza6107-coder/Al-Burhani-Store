<?php
/* ========================================
   AL BURHAN — DATABASE CONFIG
   File: Admin-Panel/config.php
   ======================================== */

define('DB_HOST',    'localhost');
define('DB_NAME',    'alburhanstore');
define('DB_USER',    'root');       
define('DB_PASS',    '');           
define('DB_CHARSET', 'utf8mb4');

/* Upload folder (relative to Admin-Panel/) */
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }
    return $pdo;
}

/* JSON response helper */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>