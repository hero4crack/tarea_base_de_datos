<?php
/**
 * Cierre de sesión seguro
 */
require_once __DIR__ . '/config/session.php';

// Limpiar todas las variables de sesión
$_SESSION = [];

// Si se usan cookies de sesión, eliminarlas
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión por completo
session_destroy();

// Redirigir a login con aviso
header('Location: index.php?logout=1');
exit;
