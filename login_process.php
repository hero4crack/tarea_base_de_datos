<?php
/**
 * Procesamiento seguro de Login
 */
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método de solicitud no permitido.'
    ]);
    exit;
}

// Verificar si hubo error al conectar a la BD
if (!isset($pdo)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar a la base de datos `control_asistencia_nomina`. Asegúrate de que MySQL esté activo en XAMPP y hayas importado el archivo .sql.'
    ]);
    exit;
}

$usuario_input  = trim($_POST['usuario'] ?? '');
$password_input = trim($_POST['password'] ?? '');

if (empty($usuario_input) || empty($password_input)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor completa todos los campos requeridos.'
    ]);
    exit;
}

try {
    // Buscar usuario por nombre de usuario o por cédula del empleado asociado
    $sql = "SELECT u.id_usuario, u.id_empleado, u.usuario, u.password, u.rol, u.estado,
                   e.nombres, e.apellidos, e.cedula, d.nombre_departamento
            FROM usuario u
            LEFT JOIN empleado e ON u.id_empleado = e.id_empleado
            LEFT JOIN departamento d ON e.id_departamento = d.id_departamento
            WHERE u.usuario = :usr OR e.cedula = :ced
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usr' => $usuario_input,
        ':ced' => $usuario_input
    ]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'El usuario o cédula ingresado no existe.'
        ]);
        exit;
    }

    // Verificar si está activo
    if ($user['estado'] !== 'Activo') {
        echo json_encode([
            'success' => false,
            'message' => 'Esta cuenta se encuentra inactiva. Contacte al administrador.'
        ]);
        exit;
    }

    // Verificar contraseña con password_verify
    if (!password_verify($password_input, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Contraseña incorrecta. Inténtalo nuevamente.'
        ]);
        exit;
    }

    // Regenerar ID de sesión por seguridad anti session fixation
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['id_usuario']     = $user['id_usuario'];
    $_SESSION['usuario']        = $user['usuario'];
    $_SESSION['rol']            = $user['rol'];
    $_SESSION['id_empleado']    = $user['id_empleado'];
    $_SESSION['nombre_completo']= !empty($user['nombres']) ? ($user['nombres'] . ' ' . $user['apellidos']) : $user['usuario'];
    $_SESSION['departamento']   = $user['nombre_departamento'] ?? 'Sin departamento';
    $_SESSION['login_time']     = date('Y-m-d H:i:s');

    // Actualizar último login
    $updateStmt = $pdo->prepare("UPDATE usuario SET ultimo_login = NOW() WHERE id_usuario = :id");
    $updateStmt->execute([':id' => $user['id_usuario']]);

    echo json_encode([
        'success'  => true,
        'message'  => '¡Credenciales correctas! Ingresando al sistema...',
        'redirect' => 'dashboard.php'
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la consulta: ' . $e->getMessage()
    ]);
    exit;
}
