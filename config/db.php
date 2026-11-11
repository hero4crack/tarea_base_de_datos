<?php
/**
 * Conexión a la Base de Datos usando PDO
 * Base de datos: control_asistencia_nomina
 */

$host     = '127.0.0.1'; // o 'localhost'
$dbname   = 'control_asistencia_nomina';
$username = 'root';
$password = ''; // Por defecto en XAMPP suele ser vacío
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Si la conexión falla y la llamada es AJAX (JSON)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error de conexión con la base de datos: ' . $e->getMessage()
        ]);
        exit;
    }
    // Si es carga normal de página
    $db_connection_error = $e->getMessage();
}
