<?php
// config/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sabong_arena_db');
define('ARENA_NAME', 'Saraet Cockpit Arena');
define('ARENA_LOCATION', 'Himamaylan City, Negros Occidental');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:12px;margin:2rem">
                <strong>Database Error:</strong> ' . $conn->connect_error . '<br><br>
                <strong>Fix:</strong> Make sure MySQL is running in XAMPP and you have imported <code>database.sql</code>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function clean($db, $val) {
    return $db->real_escape_string(strip_tags(trim($val)));
}

function jsonOut($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function qs($db, $sql) {
    $r = $db->query($sql);
    return $r ? $r->fetch_row()[0] : 0;
}
