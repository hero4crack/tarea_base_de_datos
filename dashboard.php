<?php
require_once __DIR__ . '/config/session.php';

// Validar que exista sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

// Obtener estadísticas de la base de datos si la conexión está lista
$total_deptos     = 0;
$total_empleados  = 0;
$asistencias_hoy  = 0;
$periodo_activo   = 'No definido';
$empleados_list   = [];
$asistencias_list = [];

if (isset($pdo)) {
    try {
        // Conteo Departamentos
        $stmt = $pdo->query("SELECT COUNT(*) FROM departamento");
        $total_deptos = $stmt->fetchColumn();

        // Conteo Empleados
        $stmt = $pdo->query("SELECT COUNT(*) FROM empleado");
        $total_empleados = $stmt->fetchColumn();

        // Conteo Asistencias Hoy
        $stmt = $pdo->query("SELECT COUNT(*) FROM asistencia WHERE fecha = CURRENT_DATE()");
        $asistencias_hoy = $stmt->fetchColumn();

        // Período Activo
        $stmt = $pdo->query("SELECT nombre_periodo, estado FROM periodo_nomina WHERE estado = 'Abierto' LIMIT 1");
        $periodo = $stmt->fetch();
        if ($periodo) {
            $periodo_activo = $periodo['nombre_periodo'];
        }

        // Listado de empleados con su departamento
        $stmt = $pdo->query("SELECT e.id_empleado, e.cedula, e.nombres, e.apellidos, e.tipo_empleado, 
                                    e.sueldo_base_mensual, d.nombre_departamento
                             FROM empleado e
                             LEFT JOIN departamento d ON e.id_departamento = d.id_departamento
                             ORDER BY e.id_empleado ASC LIMIT 10");
        $empleados_list = $stmt->fetchAll();

        // Asistencias recientes
        $stmt = $pdo->query("SELECT a.id_asistencia, a.fecha, a.hora_entrada, a.hora_salida, a.tipo_marcacion, a.observacion,
                                    CONCAT(e.nombres, ' ', e.apellidos) AS nombre_completo, e.cedula
                             FROM asistencia a
                             JOIN empleado e ON a.id_empleado = e.id_empleado
                             ORDER BY a.fecha DESC, a.hora_entrada DESC LIMIT 5");
        $asistencias_list = $stmt->fetchAll();

    } catch (PDOException $e) {
        // Si las tablas aún no se importaron
        $error_tablas = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Control de Asistencia y Nómina</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body.dashboard-page {
            background-color: #0b1120;
            color: #f1f5f9;
            min-height: 100vh;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        /* Barra de navegación */
        .navbar {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .navbar-brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar-brand-icon svg {
            width: 20px;
            height: 20px;
            color: #fff;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.925rem;
            display: block;
        }

        .user-role {
            font-size: 0.75rem;
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            display: inline-block;
            font-weight: 600;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-1px);
        }

        /* Contenido principal */
        .main-container {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .welcome-header {
            margin-bottom: 2rem;
            animation: cardEntrance 0.6s ease-out;
        }

        .welcome-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .welcome-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Cuadrícula de tarjetas métricas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: var(--transition);
            animation: cardEntrance 0.7s ease-out;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 26px;
            height: 26px;
        }

        .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .stat-icon.emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.15); color: #a78bfa; }
        .stat-icon.amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

        .stat-details h3 {
            font-size: 1.65rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .stat-details p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.2rem;
        }

        /* Secciones de tablas */
        .section-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            margin-bottom: 2rem;
            animation: cardEntrance 0.8s ease-out;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-count {
            background: rgba(99, 102, 241, 0.2);
            color: #818cf8;
            font-size: 0.75rem;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        /* Tabla estilizada */
        .table-responsive {
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            text-align: left;
        }

        .custom-table th {
            padding: 0.85rem 1rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .custom-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }

        .custom-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .type-badge {
            display: inline-block;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .badge-admin { background: rgba(59, 130, 246, 0.15); color: #93c5fd; }
        .badge-obrero { background: rgba(245, 158, 11, 0.15); color: #fde68a; }
        .badge-bio { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
        .badge-card { background: rgba(139, 92, 246, 0.15); color: #c4b5fd; }

        footer.dashboard-footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }
    </style>
</head>
<body class="dashboard-page">

    <!-- Navbar superior -->
    <header class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <div class="navbar-brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
            <span>Control de Asistencia y Nómina</span>
        </a>

        <div class="navbar-user">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($_SESSION['rol'] ?? 'Rol'); ?> &bull; <?php echo htmlspecialchars($_SESSION['departamento'] ?? ''); ?></span>
            </div>
            <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
                <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Salir</span>
            </a>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <main class="main-container">
        
        <!-- Saludo -->
        <div class="welcome-header">
            <h2>¡Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? $_SESSION['usuario']); ?>! 👋</h2>
            <p>Bienvenido al Sistema Integrado de Control de Asistencia y Gestión de Nómina.</p>
        </div>

        <!-- Alerta de advertencia si no se han cargado las tablas -->
        <?php if (!empty($error_tablas)): ?>
        <div class="alert-box alert-warning" style="display: flex; margin-bottom: 2rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong>Aviso de Base de Datos:</strong> Aún no se han detectado las tablas. Asegúrate de importar el archivo <code>database/control_asistencia_nomina.sql</code> en tu phpMyAdmin para ver los datos sincronizados.
            </div>
        </div>
        <?php endif; ?>

        <!-- Tarjetas Métricas -->
        <div class="stats-grid">
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-details">
                    <h3><?php echo (int)$total_empleados; ?></h3>
                    <p>Empleados Registrados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <div class="stat-details">
                    <h3><?php echo (int)$total_deptos; ?></h3>
                    <p>Departamentos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon emerald">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-details">
                    <h3><?php echo (int)$asistencias_hoy; ?></h3>
                    <p>Asistencias de Hoy</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="stat-details">
                    <h3 style="font-size: 1.15rem;"><?php echo htmlspecialchars($periodo_activo); ?></h3>
                    <p>Período de Nómina Activo</p>
                </div>
            </div>

        </div>

        <!-- Tabla: Empleados Registrados -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <span>👥 Personal de la Empresa</span>
                    <span class="badge-count"><?php echo count($empleados_list); ?> registros</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Nombres y Apellidos</th>
                            <th>Departamento</th>
                            <th>Tipo Empleado</th>
                            <th>Sueldo Base</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($empleados_list)): ?>
                            <?php foreach ($empleados_list as $emp): ?>
                            <tr>
                                <td>#<?php echo $emp['id_empleado']; ?></td>
                                <td><strong><?php echo htmlspecialchars($emp['cedula']); ?></strong></td>
                                <td><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($emp['nombre_departamento'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="type-badge <?php echo $emp['tipo_empleado'] === 'Administrativo' ? 'badge-admin' : 'badge-obrero'; ?>">
                                        <?php echo htmlspecialchars($emp['tipo_empleado']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($emp['sueldo_base_mensual'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                    No hay empleados registrados o la base de datos aún no ha sido importada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla: Últimas Asistencias -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <span>⏱️ Registro de Asistencias Recientes</span>
                    <span class="badge-count"><?php echo count($asistencias_list); ?> registros</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Cédula</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Marcación</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($asistencias_list)): ?>
                            <?php foreach ($asistencias_list as $asist): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asist['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($asist['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($asist['cedula']); ?></td>
                                <td><span style="color: #34d399; font-weight: 600;"><?php echo htmlspecialchars($asist['hora_entrada'] ?? '--:--'); ?></span></td>
                                <td><span style="color: #f87171; font-weight: 600;"><?php echo htmlspecialchars($asist['hora_salida'] ?? '--:--'); ?></span></td>
                                <td>
                                    <span class="type-badge <?php echo $asist['tipo_marcacion'] === 'Biometrico' ? 'badge-bio' : 'badge-card'; ?>">
                                        <?php echo htmlspecialchars($asist['tipo_marcacion']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($asist['observacion'] ?? 'Ninguna'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                    No hay asistencias registradas recientemente.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="dashboard-footer">
        Sistema de Control de Asistencia y Nómina &bull; Tarea Académica &bull; PHP 8.x + MySQL
    </footer>

</body>
</html>
