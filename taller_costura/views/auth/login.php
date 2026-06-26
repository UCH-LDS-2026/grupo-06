<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = $_SESSION['error_login'] ?? null;
$errorCambio = $_SESSION['error_cambio'] ?? null;
$exitoCambio = $_SESSION['exito_cambio'] ?? null;

if (!isset($_SESSION['admin_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['error_login'] = $error;
$_SESSION['error_cambio'] = $errorCambio;
$_SESSION['exito_cambio'] = $exitoCambio;

$error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
$errorCambio = $_SESSION['error_cambio'] ?? null;
unset($_SESSION['error_cambio']);
$exitoCambio = $_SESSION['exito_cambio'] ?? null;
unset($_SESSION['exito_cambio']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller — Ingresar</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/login.css">
</head>
<body>
<div class="auth-shell">

    <!-- ───────── Panel marca (izquierda) ───────── -->
    <aside class="panel-marca">
        <span class="blob blob--1" aria-hidden="true"></span>
        <span class="blob blob--2" aria-hidden="true"></span>
        <span class="blob blob--3" aria-hidden="true"></span>

        <div class="panel-marca__top">
            <div class="divisor">
                <svg viewBox="0 0 56 56" aria-hidden="true">
                    <defs>
                        <linearGradient id="divisorGrad" x1="0" y1="0" x2="56" y2="56" gradientUnits="userSpaceOnUse">
                            <stop offset="0" stop-color="#ec4f86"/>
                            <stop offset="1" stop-color="#f0793f"/>
                        </linearGradient>
                    </defs>
                    <circle cx="28" cy="28" r="26.25" fill="none" stroke="url(#divisorGrad)" stroke-width="1.5"/>
                    <g transform="translate(17,17) scale(0.92)" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="2.4"/>
                        <circle cx="6" cy="18" r="2.4"/>
                        <path d="M8.1 7.5 20 18M8.1 16.5 20 6"/>
                    </g>
                </svg>
            </div>
            <h1>Taller</h1>
            <p class="eyebrow">Sistema de gestión</p>
            <span class="divider-line" aria-hidden="true"></span>
        </div>

        <div class="panel-marca__mid">
            <svg class="hilo" viewBox="0 0 170 70" fill="none" aria-hidden="true">
                <path d="M30,18 C55,5 85,40 115,28 C135,20 150,35 158,22" stroke="url(#hiloGrad)" stroke-width="1.6" stroke-dasharray="3 6" stroke-linecap="round"/>
                <g transform="rotate(-35 22 14)">
                    <rect x="14" y="6" width="5" height="34" rx="2.5" fill="#ffffff" opacity=".9"/>
                    <ellipse cx="16.5" cy="13" rx="1.5" ry="2.3" fill="none" stroke="#46225f" stroke-width="1"/>
                </g>
                <defs>
                    <linearGradient id="hiloGrad" x1="0" y1="0" x2="170" y2="0" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#ec4f86"/>
                        <stop offset="1" stop-color="#f0793f"/>
                    </linearGradient>
                </defs>
            </svg>
            <p class="frase">Organizá tu taller,<br>cuidá cada detalle.</p>
        </div>

        <button type="button" class="link-ayuda" onclick="window.location.href='mailto:soporte@taller.com'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9.5"/>
                <path d="M9.2 9.3a2.8 2.8 0 1 1 4 2.5c-.9.5-1.4 1-1.4 2.1"/>
                <circle cx="12" cy="17" r=".4" fill="currentColor" stroke="none"/>
            </svg>
            ¿Necesitás ayuda?
        </button>
    </aside>

    <!-- ───────── Panel form (derecha) ───────── -->
    <main class="panel-form">
        <div class="wrapper">

            <div class="marca-mini">
                <div class="divisor divisor--mini">
                    <svg viewBox="0 0 44 44" aria-hidden="true">
                        <defs>
                            <linearGradient id="divisorGradMini" x1="0" y1="0" x2="44" y2="44" gradientUnits="userSpaceOnUse">
                                <stop offset="0" stop-color="#ec4f86"/>
                                <stop offset="1" stop-color="#f0793f"/>
                            </linearGradient>
                        </defs>
                        <circle cx="22" cy="22" r="20.25" fill="none" stroke="url(#divisorGradMini)" stroke-width="1.5"/>
                        <g transform="translate(13.6,13.6) scale(0.7)" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="6" r="2.4"/>
                            <circle cx="6" cy="18" r="2.4"/>
                            <path d="M8.1 7.5 20 18M8.1 16.5 20 6"/>
                        </g>
                    </svg>
                </div>
                <h2>Taller</h2>
                <p class="eyebrow">Sistema de gestión</p>
            </div>

            <!-- Card login -->
            <div class="card" id="seccion-login">
                <h2 class="card-titulo">Ingresar al sistema</h2>
                <?php if ($error): ?><div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form action="<?= BASE_URL ?>/index.php" method="POST">
                    <input type="hidden" name="accion" value="login">
                    <div class="campo">
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.4"/>
                                <path d="M5 20c0-3.6 3.1-6.2 7-6.2s7 2.6 7 6.2"/>
                            </svg>
                            <input type="email" id="email" name="email" placeholder="admin@taller.com" required autofocus>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="contrasena">Contraseña</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="9" rx="2.2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required class="con-ojo">
                            <button type="button" class="toggle-ojo" aria-label="Mostrar contraseña" onclick="toggleOjo(this)">
                                <svg class="icono-mostrar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icono-ocultar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a14 14 0 0 1-3.2 4.1M6.6 6.6C3.4 8.6 1.5 12 1.5 12s3.5 7 10.5 7c1.5 0 2.8-.3 4-.8"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary">Ingresar</button>
                </form>
                <div class="separador">¿Olvidaste tu contraseña?</div>
                <button class="btn btn--secondary" onclick="mostrarCambio()">Cambiar contraseña</button>
            </div>

            <!-- Card cambio de contraseña -->
            <div class="card" id="seccion-cambio">
                <h2 class="card-titulo">Cambiar contraseña</h2>
                <?php if ($errorCambio): ?><div class="alerta alerta-error"><?= htmlspecialchars($errorCambio) ?></div><?php endif; ?>
                <?php if ($exitoCambio): ?><div class="alerta alerta-ok"><?= htmlspecialchars($exitoCambio) ?></div><?php endif; ?>
                <form action="<?= BASE_URL ?>/index.php" method="POST">
                    <input type="hidden" name="accion" value="cambiar_contrasena">
                    <div class="campo">
                        <label for="email_cambio">Email</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.4"/>
                                <path d="M5 20c0-3.6 3.1-6.2 7-6.2s7 2.6 7 6.2"/>
                            </svg>
                            <input type="email" id="email_cambio" name="email" required>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="contrasena_actual">Contraseña actual</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="9" rx="2.2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <input type="password" id="contrasena_actual" name="contrasena_actual" required class="con-ojo">
                            <button type="button" class="toggle-ojo" aria-label="Mostrar contraseña" onclick="toggleOjo(this)">
                                <svg class="icono-mostrar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icono-ocultar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a14 14 0 0 1-3.2 4.1M6.6 6.6C3.4 8.6 1.5 12 1.5 12s3.5 7 10.5 7c1.5 0 2.8-.3 4-.8"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="nueva_contrasena">Nueva contraseña</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="9" rx="2.2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required class="con-ojo">
                            <button type="button" class="toggle-ojo" aria-label="Mostrar contraseña" onclick="toggleOjo(this)">
                                <svg class="icono-mostrar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icono-ocultar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a14 14 0 0 1-3.2 4.1M6.6 6.6C3.4 8.6 1.5 12 1.5 12s3.5 7 10.5 7c1.5 0 2.8-.3 4-.8"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                </svg>
                            </button>
                        </div>
                        <p class="hint">Mínimo 8 caracteres.</p>
                    </div>
                    <div class="campo">
                        <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                        <div class="input-wrap">
                            <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="9" rx="2.2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required class="con-ojo">
                            <button type="button" class="toggle-ojo" aria-label="Mostrar contraseña" onclick="toggleOjo(this)">
                                <svg class="icono-mostrar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icono-ocultar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a14 14 0 0 1-3.2 4.1M6.6 6.6C3.4 8.6 1.5 12 1.5 12s3.5 7 10.5 7c1.5 0 2.8-.3 4-.8"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary">Guardar</button>
                </form>
                <div class="separador"></div>
                <button class="btn btn--secondary" onclick="mostrarLogin()">← Volver al inicio de sesión</button>
            </div>

        </div>
    </main>
</div>

<script>
    function mostrarCambio() {
        document.getElementById('seccion-login').style.display = 'none';
        document.getElementById('seccion-cambio').style.display = 'block';
    }

    function mostrarLogin() {
        document.getElementById('seccion-cambio').style.display = 'none';
        document.getElementById('seccion-login').style.display = 'block';
    }

    function toggleOjo(btn) {
        const wrap = btn.closest('.input-wrap');
        const input = wrap.querySelector('input');
        const mostrar = btn.querySelector('.icono-mostrar');
        const ocultar = btn.querySelector('.icono-ocultar');
        const esOculta = input.type === 'password';
        input.type = esOculta ? 'text' : 'password';
        mostrar.style.display = esOculta ? 'none' : '';
        ocultar.style.display = esOculta ? '' : 'none';
        btn.setAttribute('aria-label', esOculta ? 'Ocultar contraseña' : 'Mostrar contraseña');
    }

    // Si hay error o éxito tras cambiar contraseña, mostrar ese card directamente
    <?php if ($errorCambio || $exitoCambio): ?>
        mostrarCambio();
    <?php endif; ?>
</script>
</body>
</html>