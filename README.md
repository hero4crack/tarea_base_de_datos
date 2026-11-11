# Sistema de Control de Asistencia y Nómina 🚀

Proyecto desarrollado en **PHP 8 + MySQL** con diseño profesional, interfaz moderna con estilo glassmorphism, efectos visuales y animaciones fluidas para entrega académica.

---

## 📁 Estructura del Proyecto

```
control_asistencia_nomina/
│
├── config/
│   └── db.php                  # Conexión PDO segura con UTF-8 y manejo de excepciones
│
├── database/
│   └── control_asistencia_nomina.sql  # Script SQL completo (Tablas + Relaciones + Datos de prueba)
│
├── assets/
│   ├── css/
│   │   └── style.css           # Estilos modernos, Glassmorphism y keyframes de animación
│   └── js/
│       └── main.js             # Validación, fetch AJAX, alternar contraseña y spinner
│
├── index.php                   # Vista de Login con diseño corporativo y credenciales demo
├── login_process.php           # Backend seguro de autenticación (password_verify y sesiones)
├── dashboard.php               # Panel principal post-login con métricas y tablas del sistema
├── logout.php                  # Cierre seguro de sesión
└── README.md                   # Esta guía
```

---

## 🗄️ Estructura de la Base de Datos (`control_asistencia_nomina`)

El diseño de la base de datos cumple al 100% con el diccionario de datos del diagrama relacional:

1. **`departamento`**: Almacena las áreas de la empresa (`id_departamento`, `nombre_departamento`, `descripcion`).
2. **`empleado`**: Ficha del trabajador con su cédula, nombres, tipo (Administrativo/Obrero), sueldo base y clave foránea al departamento.
3. **`usuario`**: Tabla de autenticación vinculada al empleado (`usuario`, `password` hasheada con BCRYPT, `rol`, `estado`).
4. **`asistencia`**: Control diario de marcaciones (`hora_entrada`, `hora_salida`, `tipo_marcacion` Enum: Biométrico/Tarjeta, observación) con clave única compuesta `(id_empleado, fecha)`.
5. **`bono`**: Registro de bonificaciones otorgadas a los empleados.
6. **`concepto_nomina`**: Catálogo de asignaciones y deducciones legales y contractuales.
7. **`periodo_nomina`**: Períodos quincenales/mensuales de nómina (Abierto/Cerrado).
8. **`detalle_nomina`**: Detalle del cálculo de conceptos aplicados a cada empleado en cada período.

---

## ⚙️ Pasos para Instalar y Ejecutar en XAMPP

### Paso 1: Importar la Base de Datos en phpMyAdmin
1. Abre el **XAMPP Control Panel** e inicia los módulos **Apache** y **MySQL**.
2. Entra a tu navegador e ingresa a:  
   👉 [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. En la barra superior, haz clic en la pestaña **Importar** (o crea la base de datos `control_asistencia_nomina`).
4. Selecciona el archivo:  
   `database/control_asistencia_nomina.sql`
5. Presiona **Importar** (o **Continuar**). ¡Listo! Se crearán todas las tablas y datos de muestra.

### Paso 2: Ejecutar el Proyecto

#### Opción A (Recomendada con XAMPP):
Copia o mueve la carpeta `control_asistencia_nomina` dentro de:  
`C:\xampp\htdocs\`  
Luego abre tu navegador en:  
👉 **`http://localhost/control_asistencia_nomina/`**

#### Opción B (Servidor Integrado de PHP rápido):
Abre una terminal en la carpeta del proyecto y ejecuta:
```bash
php -S localhost:8000
```
Y abre:  
👉 **`http://localhost:8000/`**

---

## 🔑 Credenciales de Acceso para la Tarea

| Usuario / Cédula | Contraseña | Rol | Nombre Empleado |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Administrador | Carlos Eduardo Mendoza |
| **`recursoshumanos`** | `admin123` | Supervisor | Ana María García |
| **`V-10293847`** | `admin123` | Administrador | Acceso directo con Cédula |

*(En la pantalla de inicio de sesión tienes botones interactivos de 1 clic para autocompletar estas credenciales durante tu exposición).*

---

## ✨ Características y Animaciones Implementadas
- **Orbes de fondo flotantes**: Animación CSS pura con `@keyframes floatOrb` suave y ligera.
- **Glassmorphism**: Efecto translúcido moderno con `backdrop-filter: blur(20px)` y bordes sutiles.
- **Micro-interacciones en Inputs**: Brillo y enfoque con gradiente azul-índigo corporativo.
- **Alternar Contraseña**: Botón para ver y ocultar la clave con animación de icono SVG.
- **Feedback de Error / Éxito**: Animación de sacudida (*shake*) ante credenciales incorrectas.
- **Botón con Spinner**: Indicador de carga asíncrono mientras valida la información con AJAX (sin recargar la página).
- **Seguridad Robusta**: Consultas preparadas contra inyección SQL (PDO) y contraseñas cifradas con `password_hash()` de PHP.
