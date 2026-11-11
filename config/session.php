<?php
/**
 * Configuración segura de Sesión
 */
if (session_status() === PHP_SESSION_NONE) {
    $local_sessions = __DIR__ . '/../sessions';
    if (!is_dir($local_sessions)) {
        @mkdir($local_sessions, 0777, true);
    }
    // Asegurar directorio local de sesiones para evitar problemas de permisos
    @session_save_path($local_sessions);
    session_start();
}
