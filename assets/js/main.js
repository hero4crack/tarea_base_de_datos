/**
 * Script de interacción y animaciones para el Login
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

    // 1. Mostrar / Ocultar Contraseña con icono dinámico
    if (togglePassBtn && passInput) {
        togglePassBtn.addEventListener('click', () => {
            const isPassword = passInput.getAttribute('type') === 'password';
            passInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Alternar icono SVG
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
            
            // Efecto visual de resaltado
            userInput.classList.add('highlight-glow');
            passInput.classList.add('highlight-glow');
            setTimeout(() => {
                userInput.classList.remove('highlight-glow');
                passInput.classList.remove('highlight-glow');
            }, 600);
        }
    };

    // 3. Manejo del Formulario con AJAX
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Ocultar alertas previas
            hideAlert();

            const usuario = userInput.value.trim();
            const password = passInput.value.trim();

            if (!usuario || !password) {
                showAlert('Por favor ingresa tu usuario y contraseña.', 'error');
                triggerShake();
                return;
            }

            // Activar estado de carga en el botón
            setLoading(true);

            try {
                const formData = new FormData(loginForm);

                const response = await fetch('login_process.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message || 'Credenciales válidas. Redirigiendo...', 'success');
                    
                    // Animación suave de salida antes de redirigir
                    loginCard.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    loginCard.style.opacity = '0.7';
                    loginCard.style.transform = 'scale(0.98)';

                    setTimeout(() => {
                        window.location.href = data.redirect || 'dashboard.php';
                    }, 1000);
                } else {
                    setLoading(false);
                    showAlert(data.message || 'Error al iniciar sesión.', 'error');
                    triggerShake();
                    passInput.focus();
                }
            } catch (error) {
                setLoading(false);
                showAlert('Error de comunicación con el servidor. Verifica que Apache y MySQL estén iniciados.', 'error');
                triggerShake();
                console.error('Login error:', error);
            }
        });
    }

    // Función para activar o desactivar el botón con spinner
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

    // Función para mostrar alertas estilizadas
    function showAlert(msg, type = 'error') {
        if (!alertBox || !alertMessage) return;
        
        alertBox.className = 'alert-box alert-' + type;
        alertMessage.textContent = msg;
        alertBox.style.display = 'flex';
    }

    // Ocultar alerta
    function hideAlert() {
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }

    // Animación de sacudida en caso de error
    function triggerShake() {
        if (!loginCard) return;
        loginCard.classList.remove('shake-animation');
        // Forzar reflow del DOM para reiniciar animación
        void loginCard.offsetWidth;
        loginCard.classList.add('shake-animation');
    }
});
