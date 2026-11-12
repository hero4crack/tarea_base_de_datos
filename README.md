# Sistema de Control de Asistencia y Nómina 🚀
### Proyecto PHP 8 + MySQL | Compatible con XAMPP y GitHub Pages

Sistema de gestión laboral y control de asistencia con interfaz moderna estilo **Glassmorphism**, micro-interacciones, efectos visuales y animaciones fluidas.

---

## 🌐 Publicación en GitHub Pages (Para compartir con tus compañeros)

Como **GitHub Pages** aloja sitios estáticos en la nube (no ejecuta servidores locales de PHP ni motores de MySQL directamente), este proyecto incluye una **versión híbrida dual inteligente**:

- **En XAMPP (`http://localhost/tarea_base_de_datos/`):**  
  Ejecuta el backend real en **PHP 8 + MariaDB**, procesando las consultas SQL a la base de datos `control_asistencia_nomina` mediante PDO y contraseñas cifradas con BCRYPT.
  
- **En GitHub Pages (`https://tu-usuario.github.io/tu-repositorio/`):**  
  Ejecuta la **Live Demo interactiva (`index.html` y `dashboard.html`)** con las mismas animaciones, login interactivo funcional, tablas, métricas y un visor integrado para que tus compañeros y el profesor puedan ver el código PHP y el script SQL directamente en el navegador.

---

## 🚀 Pasos para subirlo a GitHub y activar GitHub Pages

### 1. Inicializar Git y subir tu proyecto
Abre una terminal en tu carpeta:
```bash
cd C:\xampp\htdocs\tarea_base_de_datos
git init
git add .
git commit -m "feat: Sistema de Asistencia y Nómina completo con soporte para GitHub Pages"
```

Crea un repositorio en tu cuenta de GitHub (ejemplo: `tarea_base_de_datos`) y conéctalo:
```bash
git branch -M main
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git push -u origin main
```

### 2. Activar GitHub Pages en 1 minuto
1. Entra a tu repositorio en GitHub.
2. Ve a la pestaña **Settings** (Configuración) en la parte superior.
3. En el menú izquierdo, haz clic en **Pages**.
4. En **Build and deployment > Branch**, selecciona:
   - Rama: **`main`**
   - Carpeta: **`/ (root)`**
5. Haz clic en **Save** (Guardar).
6. En un par de minutos, GitHub te dará tu enlace público:  
   👉 `https://TU_USUARIO.github.io/TU_REPOSITORIO/`

---

## 🔑 Credenciales para iniciar sesión (Tanto en Local como en GitHub Pages)

Puedes hacer clic en los botones interactivos en la pantalla de login para rellenar los campos al instante:

| Usuario o Cédula | Contraseña | Rol | Nombre del Empleado |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | Administrador | Carlos Eduardo Mendoza |
| **`recursoshumanos`** | `admin123` | Supervisor | Ana María García |
| **`V-10293847`** *(Cédula)* | `admin123` | Administrador | Carlos Eduardo Mendoza |

---

## 📁 Estructura del Proyecto

```
tarea_base_de_datos/
│
├── index.html                  # Entrada para GitHub Pages (Animaciones + Login interactivo)
├── dashboard.html              # Panel de control para GitHub Pages con Visor de Código PHP/SQL
├── index.php                   # Entrada para XAMPP con backend PHP
├── login_process.php           # Autenticación segura PHP + PDO + password_verify()
├── dashboard.php               # Panel de control PHP conectado a MySQL
├── logout.php                  # Cierre de sesión PHP
│
├── config/
│   ├── db.php                  # Conexión PDO a MariaDB
│   └── session.php             # Configuración segura de sesiones
│
├── database/
│   └── control_asistencia_nomina.sql # 7 tablas del diagrama relacional + tabla usuarios + datos
│
├── assets/
│   ├── css/style.css           # Estilos Glassmorphism, orbes flotantes y keyframes
│   └── js/main.js              # Validación, AJAX, alternar contraseña y spinner
```

---

## 🗄️ Base de Datos (`control_asistencia_nomina`)

Incluye las tablas del diccionario de datos:
1. `departamento`
2. `empleado`
3. `usuario`
4. `asistencia` (Índice único diario por empleado y fecha)
5. `bono`
6. `concepto_nomina`
7. `periodo_nomina`
8. `detalle_nomina`
