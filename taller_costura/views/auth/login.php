<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
 
// Si ya hay sesión activa, redirigir
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: /sistema_costura/grupo-06/taller_costura/views/encargos/index.php');
    exit;
}
 
$error  = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller — Ingresar</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
        :root {
            --crema:    #f5f0e8;
            --cafe:     #3b2a1a;
            --cafe-mid: #7a5c3e;
            --dorado:   #c49a4a;
            --error:    #b94040;
            --borde:    #d9cfc0;
        }
 
        body {
            min-height: 100vh;
            background-color: var(--crema);
            background-image:
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 18px,
                    rgba(196,154,74,0.07) 18px,
                    rgba(196,154,74,0.07) 19px
                );
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
            color: var(--cafe);
        }
 
        .wrapper {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
 
        .marca {
            text-align: center;
            margin-bottom: 2rem;
        }
        .marca-icono { font-size: 2.2rem; line-height: 1; margin-bottom: .4rem; }
        .marca h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--cafe);
        }
        .marca p {
            font-size: .82rem;
            font-weight: 300;
            color: var(--cafe-mid);
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-top: .2rem;
        }
 
        .card {
            background: #fff;
            border: 1px solid var(--borde);
            border-radius: 4px;
            padding: 2.2rem 2rem;
            box-shadow: 0 2px 24px rgba(59,42,26,.07);
        }
        .card-titulo {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            margin-bottom: 1.6rem;
            padding-bottom: .8rem;
            border-bottom: 1px solid var(--borde);
            color: var(--cafe);
        }
 
        .alerta {
            font-size: .85rem;
            padding: .7rem 1rem;
            border-radius: 3px;
            margin-bottom: 1.2rem;
        }
        .alerta-error { background: #fdf1f1; border: 1px solid #e8c4c4; color: var(--error); }
 
        .campo { margin-bottom: 1.2rem; }
 
        label {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--cafe-mid);
            margin-bottom: .45rem;
        }
 
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: .65rem .9rem;
            border: 1px solid var(--borde);
            border-radius: 3px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            color: var(--cafe);
            background: var(--crema);
            transition: border-color .2s;
            outline: none;
        }
        input:focus { border-color: var(--dorado); background: #fff; }
 
        .btn {
            width: 100%;
            padding: .75rem;
            border: none;
            border-radius: 3px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 500;
            letter-spacing: .06em;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
        }
        .btn:active { transform: scale(.98); }
        .btn-principal { background: var(--cafe); color: var(--crema); margin-top: .4rem; }
        .btn-principal:hover { opacity: .88; }
        .btn-secundario {
            background: transparent;
            color: var(--cafe-mid);
            border: 1px solid var(--borde);
            margin-top: .6rem;
        }
        .btn-secundario:hover { border-color: var(--cafe-mid); }
 
        .separador {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin: 1.8rem 0 1.4rem;
            font-size: .78rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--cafe-mid);
        }
        .separador::before,
        .separador::after { content: ''; flex: 1; height: 1px; background: var(--borde); }
 
        #seccion-cambio { display: none; }
        #seccion-cambio.visible { display: block; }
        .hint { font-size: .78rem; color: var(--cafe-mid); margin-top: .35rem; }
    </style>
</head>
<body>
 
<div class="wrapper">
 
    <div class="marca">
        <div class="marca-icono">🧵</div>
        <h1>Taller de Costura</h1>
        <p>Panel de administración</p>
    </div>
 
    <!-- Formulario de login -->
    <div class="card">
        <h2 class="card-titulo">Ingresar al sistema</h2>
 
        <?php if ($error): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
 
        <form action="/sistema_costura/grupo-06/taller_costura/controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="login">
 
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="admin@taller.com" required autofocus>
            </div>
 
            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena"
                       placeholder="••••••••" required>
            </div>
 
            <button type="submit" class="btn btn-principal">Ingresar</button>
        </form>
 
        <div class="separador">¿Olvidaste tu contraseña?</div>
 
        <button class="btn btn-secundario" onclick="toggleCambio()">
            Cambiar contraseña
        </button>
    </div>
 
    <!-- Formulario cambio de contraseña -->
    <div class="card" id="seccion-cambio" style="margin-top:1rem;">
        <h2 class="card-titulo">Cambiar contraseña</h2>
 
        <?php
        $errorCambio = $_SESSION['error_cambio'] ?? null;
        $exitoCambio = $_SESSION['exito_cambio'] ?? null;
        unset($_SESSION['error_cambio'], $_SESSION['exito_cambio']);
        ?>
 
        <?php if ($errorCambio): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($errorCambio) ?></div>
        <?php endif; ?>
        <?php if ($exitoCambio): ?>
            <div class="alerta" style="background:#f1fdf5;border:1px solid #b6dfc4;color:#2e6b45;">
                <?= htmlspecialchars($exitoCambio) ?>
            </div>
        <?php endif; ?>
 
        <form action="/sistema_costura/grupo-06/taller_costura/controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="cambiar_contrasena">
 
            <div class="campo">
                <label for="email_cambio">Email del administrador</label>
                <input type="email" id="email_cambio" name="email" placeholder="admin@taller.com" required>
            </div>
 
            <div class="campo">
                <label for="contrasena_actual">Contraseña actual</label>
                <input type="password" id="contrasena_actual" name="contrasena_actual" placeholder="••••••••" required>
            </div>
 
            <div class="campo">
                <label for="nueva_contrasena">Nueva contraseña</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" placeholder="••••••••" required>
                <p class="hint">Mínimo 8 caracteres.</p>
            </div>
 
            <div class="campo">
                <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="••••••••" required>
            </div>
 
            <button type="submit" class="btn btn-principal">Guardar nueva contraseña</button>
        </form>
    </div>
 
</div>
 
<script>
    function toggleCambio() {
        document.getElementById('seccion-cambio').classList.toggle('visible');
    }
    <?php if ($errorCambio || $exitoCambio): ?>
        document.getElementById('seccion-cambio').classList.add('visible');
    <?php endif; ?>
</script>
 
</body>
</html>