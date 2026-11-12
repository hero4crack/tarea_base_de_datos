/**
 * Script de interacción y animaciones para el Login
 * Compatible con XAMPP (PHP + MySQL) y GitHub Pages (Live Demo interactivo)
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const userInput = document.getElementById('usuario');
    const passInput = document.getElementById('password');
    const togglePassBtn = document.getElementById('togglePassword');
    const submitBtn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alertBox');
    const alertMessage = document.getElementById('alertMessage');
    const loginCard = document.querySelector('.login-card');

    // Revisar si viene de logout
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('logout')) {
        showAlert('Has cerrado sesión exitosamente.', 'success');
    }

    // 1. Mostrar / Ocultar Contraseña con icono dinámico
    if (togglePassBtn && passInput) {
        togglePassBtn.addEventListener('click', () => {
            const isPassword = passInput.getAttribute('type') === 'password';
            passInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            const eyeIcon = togglePassBtn.querySelector('.icon-eye');
            const eyeOffIcon = togglePassBtn.querySelector('.icon-eye-off');
            if (eyeIcon && eyeOffIcon) {
                if (isPassword) {
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                } else {
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                }
            }
        });
    }

    // 2. Click en Pills de Credenciales Demo (para facilitar pruebas)
    window.setDemoCredentials = function(username, pass) {
        if (userInput && passInput) {
            userInput.value = username;
            passInput.value = pass;
            userInput.focus();
            
            userInput.classList.add('highlight-glow');
            passInput.classList.add('highlight-glow');
            setTimeout(() => {
                userInput.classList.remove('highlight-glow');
                passInput.classList.remove('highlight-glow');
            }, 600);
        }
    };

    // Base de datos de prueba para GitHub Pages (Simulación interactiva 100% fiel)
    const mockUsers = [
        {
            usuario: 'admin',
            cedula: 'V-10293847',
            password: 'admin123',
            nombre: 'Carlos Eduardo Mendoza Castillo',
            rol: 'Administrador',
            depto: 'Tecnología e Informática'
        },
        {
            usuario: 'recursoshumanos',
            cedula: 'V-18765432',
            password: 'admin123',
            nombre: 'Ana María García Salcedo',
            rol: 'Supervisor',
            depto: 'Recursos Humanos'
        }
    ];

    // Detectar si estamos en un entorno estático (GitHub Pages o archivo .html)
    const isStaticEnv = window.location.protocol === 'file:' || 
                        window.location.hostname.includes('github.io') ||
                        window.location.pathname.endsWith('.html');

    // 3. Manejo del Formulario con AJAX
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();

            const usuario = userInput.value.trim();
            const password = passInput.value.trim();

            if (!usuario || !password) {
                showAlert('Por favor ingresa tu usuario y contraseña.', 'error');
                triggerShake();
                return;
            }

            setLoading(true);

            // Si es GitHub Pages o entorno estático, validar con simulación interactiva
            if (isStaticEnv) {
                setTimeout(() => {
                    const found = mockUsers.find(u => 
                        (u.usuario.toLowerCase() === usuario.toLowerCase() || u.cedula.toLowerCase() === usuario.toLowerCase()) &&
                        u.password === password
                    );

                    if (found) {
                        sessionStorage.setItem('auth_user', found.nombre);
                        sessionStorage.setItem('auth_role', found.rol);
                        sessionStorage.setItem('auth_depto', found.depto);

                        showAlert('¡Credenciales correctas! Ingresando al sistema...', 'success');
                        animateExitAndRedirect('dashboard.html');
                    } else {
                        setLoading(false);
                        showAlert('Usuario o contraseña incorrectos.', 'error');
                        triggerShake();
                        passInput.focus();
                    }
                }, 700);
                return;
            }

            // Si es XAMPP (servidor PHP)
            try {
                const formData = new FormData(loginForm);
                const response = await fetch('login_process.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message || 'Credenciales válidas. Redirigiendo...', 'success');
                    animateExitAndRedirect(data.redirect || 'dashboard.php');
                } else {
                    setLoading(false);
                    showAlert(data.message || 'Error al iniciar sesión.', 'error');
                    triggerShake();
                    passInput.focus();
                }
            } catch (error) {
                // Fallback automático por si se abre como archivo o falla la conexión PHP
                const found = mockUsers.find(u => 
                    (u.usuario.toLowerCase() === usuario.toLowerCase() || u.cedula.toLowerCase() === usuario.toLowerCase()) &&
                    u.password === password
                );

                if (found) {
                    sessionStorage.setItem('auth_user', found.nombre);
                    sessionStorage.setItem('auth_role', found.rol);
                    sessionStorage.setItem('auth_depto', found.depto);
                    showAlert('¡Ingresando en modo demostración interactiva!', 'success');
                    animateExitAndRedirect('dashboard.html');
                } else {
                    setLoading(false);
                    showAlert('Contraseña incorrecta o error en el servidor.', 'error');
                    triggerShake();
                }
            }
        });
    }

    function animateExitAndRedirect(targetUrl) {
        if (loginCard) {
            loginCard.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            loginCard.style.opacity = '0.7';
            loginCard.style.transform = 'scale(0.98)';
        }
        setTimeout(() => {
            window.location.href = targetUrl;
        }, 900);
    }

    function setLoading(isLoading) {
        if (!submitBtn) return;
        submitBtn.disabled = isLoading;
        const btnText = submitBtn.querySelector('.btn-text');
        if (isLoading) {
            submitBtn.classList.add('is-loading');
            if (btnText) btnText.textContent = 'Verificando...';
        } else {
            submitBtn.classList.remove('is-loading');
            if (btnText) btnText.textContent = 'Iniciar Sesión';
        }
    }

    function showAlert(msg, type = 'error') {
        if (!alertBox || !alertMessage) return;
        alertBox.className = 'alert-box alert-' + type;
        alertMessage.textContent = msg;
        alertBox.style.display = 'flex';
    }

    function hideAlert() {
        if (alertBox) alertBox.style.display = 'none';
    }

    function triggerShake() {
        if (!loginCard) return;
        loginCard.classList.remove('shake-animation');
        void loginCard.offsetWidth;
        loginCard.classList.add('shake-animation');
    }
});
