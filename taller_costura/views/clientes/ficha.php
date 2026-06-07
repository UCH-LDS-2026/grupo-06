<?php
require_once BASE_PATH . '/models/Cliente.php';
require_once BASE_PATH . '/models/FichaCliente.php';
require_once BASE_PATH . '/models/Encargo.php';
 
$clienteId = (int)($_GET['id'] ?? 0);
 
if ($clienteId === 0) {
    header('Location: ' . BASE_URL . '/index.php?page=clientes');
    exit;
}
 
$cliente = Cliente::getById($clienteId);
 
if ($cliente === null) {
    header('Location: ' . BASE_URL . '/index.php?page=clientes');
    exit;
}
 
$ficha = FichaCliente::getByClienteId($clienteId);
 
// Traer encargos del cliente
$db      = Database::getInstance()->getConnection();
$encargo = new Encargo($db);
$stmt    = $encargo->getByClienteId($clienteId);
$encargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
// Calcular resumen
$totalEncargos  = count($encargos);
$activos        = count(array_filter($encargos, fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso'])));
$saldoPendiente = array_sum(array_map(fn($e) => $e['monto_total'] - $e['sena'], $encargos));
 
$exito = $_SESSION['exito_cliente'] ?? null;
$error = $_SESSION['error_cliente'] ?? null;
unset($_SESSION['exito_cliente'], $_SESSION['error_cliente']);
 
$modoEdicion = isset($_GET['editar']) && $_GET['editar'] === '1';
?>
 
<style>
    :root {
        --crema:        #FAF8F5;
        --blanco:       #FFFFFF;
        --cafe:         #2C1810;
        --cafe-mid:     #5C4A3A;
        --cafe-light:   #8B7355;
        --marron:       #7D4E2F;
        --borde:        #EDE8E0;
        --alerta-bg:    #FEF9EE;
        --alerta-borde: #E8D5A3;
    }
 
    .ficha-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }
 
    .volver {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--cafe-mid); text-decoration: none; font-size: .88rem;
        margin-bottom: 16px; transition: color .2s;
    }
    .volver:hover { color: var(--cafe); }
 
    .cliente-titulo {
        font-family: 'Playfair Display', serif;
        font-size: 2.4rem; font-weight: 400;
        color: var(--cafe); margin-bottom: 24px; line-height: 1.1;
    }
 
    .card {
        background: var(--blanco); border: 1px solid var(--borde);
        border-radius: 12px; padding: 24px; margin-bottom: 20px;
    }
 
    .card-titulo {
        font-family: 'Playfair Display', serif;
        font-size: 1.2rem; font-weight: 400; margin-bottom: 20px; color: var(--cafe);
    }
 
    /* Info contacto */
    .contacto-lista { display: flex; flex-direction: column; gap: 16px; }
    .contacto-item { display: flex; align-items: center; gap: 14px; }
    .contacto-icono {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--crema); border: 1px solid var(--borde);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .contacto-label { font-size: .78rem; color: var(--cafe-light); margin-bottom: 2px; }
    .contacto-valor { font-size: .95rem; color: var(--cafe); font-weight: 500; }
 
    /* Medidas */
    .medidas-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
    }
    .medidas-titulo {
        display: flex; align-items: center; gap: 10px;
        font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 400;
    }
    .btn-editar-medidas {
        font-size: .83rem; color: var(--cafe-mid); text-decoration: none;
        transition: color .2s; background: none; border: none; cursor: pointer;
        font-family: 'Inter', sans-serif;
    }
    .btn-editar-medidas:hover { color: var(--marron); }
 
    .medidas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .medida-card {
        background: var(--crema); border: 1px solid var(--borde);
        border-radius: 8px; padding: 14px 16px;
    }
    .medida-label { font-size: .78rem; color: var(--cafe-light); margin-bottom: 6px; }
    .medida-valor { font-size: 1.3rem; color: var(--cafe); }
    .medida-valor strong { font-size: 1.6rem; font-weight: 500; font-family: 'Playfair Display', serif; }
    .medida-valor span { font-size: .85rem; color: var(--cafe-mid); margin-left: 2px; }
 
    .sin-medidas {
        text-align: center; padding: 2rem; color: var(--cafe-light); font-size: .9rem;
    }
 
    /* Formulario edición medidas */
    .form-medidas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { margin-bottom: 0; }
    .form-group label { display: block; font-size: .78rem; color: var(--cafe-light); margin-bottom: 6px; }
    .input-cm { position: relative; }
    .input-cm input {
        width: 100%; padding: .55rem .9rem; padding-right: 2.5rem;
        border: 1px solid var(--borde); border-radius: 8px;
        background: var(--blanco); font-size: .9rem; color: var(--cafe);
        outline: none; transition: border-color .2s; font-family: 'Inter', sans-serif;
    }
    .input-cm input:focus { border-color: var(--marron); }
    .input-cm .cm-label {
        position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
        font-size: .78rem; color: var(--cafe-light); pointer-events: none;
    }
    .form-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
    .btn-cancelar {
        padding: .55rem 1.1rem; background: transparent; border: 1px solid var(--borde);
        border-radius: 8px; font-size: .85rem; color: var(--cafe-mid); cursor: pointer;
        font-family: 'Inter', sans-serif; text-decoration: none;
    }
    .btn-guardar {
        padding: .55rem 1.2rem; background: var(--marron); border: none;
        border-radius: 8px; font-size: .85rem; font-weight: 500; color: #fff;
        cursor: pointer; font-family: 'Inter', sans-serif;
    }
    .btn-guardar:hover { opacity: .88; }
 
    /* Panel derecho */
    .panel-derecho { display: flex; flex-direction: column; gap: 20px; }
 
    /* Encargos */
    .encargos-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;
    }
    .encargos-titulo { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 400; }
    .btn-nuevo-encargo {
        width: 30px; height: 30px; border-radius: 50%; background: var(--marron);
        color: #fff; border: none; font-size: 1.1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none; transition: opacity .2s;
    }
    .btn-nuevo-encargo:hover { opacity: .88; }
 
    .encargo-item {
        border: 1px solid var(--borde); border-radius: 8px; padding: 14px 16px; margin-bottom: 10px;
    }
    .encargo-item:last-child { margin-bottom: 0; }
    .encargo-tipo { font-size: .95rem; font-weight: 500; color: var(--cafe); margin-bottom: 4px; }
    .encargo-fecha { font-size: .78rem; color: var(--cafe-light); margin-bottom: 8px; }
    .encargo-bottom { display: flex; align-items: center; justify-content: space-between; }
    .encargo-monto { font-size: .9rem; color: var(--cafe-mid); }
 
    .estado-badge {
        font-size: .72rem; font-weight: 500; padding: .25rem .7rem;
        border-radius: 20px; white-space: nowrap;
    }
    .estado-pendiente   { background: #FEF3E2; color: #B45309; }
    .estado-en_proceso  { background: #EFF6FF; color: #1D4ED8; }
    .estado-listo       { background: #F0FDF4; color: #15803D; }
    .estado-entregado   { background: #F3F4F6; color: #6B7280; }
 
    .sin-encargos { font-size: .85rem; color: var(--cafe-light); text-align: center; padding: 1rem; }
 
    /* Resumen */
    .resumen-fila {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--borde); font-size: .9rem;
    }
    .resumen-fila:last-child { border-bottom: none; padding-bottom: 0; }
    .resumen-label { color: var(--cafe-mid); }
    .resumen-valor { font-weight: 500; color: var(--cafe); }
    .resumen-valor.pendiente { color: var(--marron); font-size: 1rem; }
 
    /* Alertas */
    .alerta { padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 20px; }
    .alerta-ok  { background: #f0faf4; border: 1px solid #b6dfc4; color: #2e6b45; }
    .alerta-err { background: #fdf1f1; border: 1px solid #e8c4c4; color: #b94040; }
 
    /* Acciones cliente */
    .acciones-cliente { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .btn-accion {
        padding: .5rem 1rem; border-radius: 8px; font-size: .83rem;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all .2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-accion-editar {
        background: var(--blanco); border: 1px solid var(--borde); color: var(--cafe-mid);
    }
    .btn-accion-editar:hover { border-color: var(--marron); color: var(--marron); }
    .btn-accion-eliminar {
        background: var(--blanco); border: 1px solid #e8c4c4; color: #b94040;
    }
    .btn-accion-eliminar:hover { background: #fdf1f1; }
</style>
 
<?php if ($exito): ?>
    <div class="alerta alerta-ok"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta alerta-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
 
<a href="<?= BASE_URL ?>/index.php?page=clientes" class="volver">← Volver a Clientes</a>
 
<h1 class="cliente-titulo"><?= htmlspecialchars($cliente->getNombre()) ?></h1>
 
<div class="ficha-layout">
 
    <!-- ── COLUMNA IZQUIERDA ── -->
    <div>
 
        <!-- Información de Contacto -->
        <div class="card">
            <div class="card-titulo">Información de Contacto</div>
            <div class="contacto-lista">
                <div class="contacto-item">
                    <div class="contacto-icono">📞</div>
                    <div>
                        <div class="contacto-label">Teléfono</div>
                        <div class="contacto-valor"><?= htmlspecialchars($cliente->getTelefono() ?: '—') ?></div>
                    </div>
                </div>
                <div class="contacto-item">
                    <div class="contacto-icono">✉️</div>
                    <div>
                        <div class="contacto-label">Email</div>
                        <div class="contacto-valor"><?= htmlspecialchars($cliente->getEmail() ?: '—') ?></div>
                    </div>
                </div>
                <div class="contacto-item">
                    <div class="contacto-icono">📅</div>
                    <div>
                        <div class="contacto-label">Cliente desde</div>
                        <div class="contacto-valor">
                            <?= date('d \d\e F \d\e Y', strtotime($cliente->getCreatedAt())) ?>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Botones editar/eliminar cliente -->
            <div class="acciones-cliente" style="margin-top:20px; margin-bottom:0">
                <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar_cliente=1"
                   class="btn-accion btn-accion-editar">✏️ Editar datos</a>
                <form method="POST" action="<?= BASE_URL ?>/index.php"
                      onsubmit="return confirm('¿Eliminar esta clienta? Esta acción no se puede deshacer.')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= $cliente->getId() ?>">
                    <button type="submit" class="btn-accion btn-accion-eliminar">🗑 Eliminar clienta</button>
                </form>
            </div>
        </div>
 
        <!-- Medidas -->
        <div class="card">
            <div class="medidas-header">
                <div class="medidas-titulo">
                    📐 Medidas Guardadas
                </div>
                <?php if (!$modoEdicion): ?>
                    <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1"
                       class="btn-editar-medidas">Editar medidas</a>
                <?php endif; ?>
            </div>
 
            <?php if ($modoEdicion): ?>
                <!-- Formulario edición -->
                <form method="POST" action="<?= BASE_URL ?>/index.php">
                    <input type="hidden" name="accion" value="guardar_ficha">
                    <input type="hidden" name="cliente_id" value="<?= $cliente->getId() ?>">
                    <div class="form-medidas-grid">
                        <?php
                        $campos = [
                            'contorno_pecho'   => 'Contorno de Busto',
                            'contorno_cintura' => 'Contorno de Cintura',
                            'contorno_cadera'  => 'Contorno de Cadera',
                            'largo_espalda'    => 'Largo de Espalda',
                            'largo_manga'      => 'Largo de Manga',
                            'largo_pantalon'   => 'Largo de Pantalón',
                        ];
                        $getters = [
                            'contorno_pecho'   => 'getContornoPecho',
                            'contorno_cintura' => 'getContornoCintura',
                            'contorno_cadera'  => 'getContornoCadera',
                            'largo_espalda'    => 'getLargoEspalda',
                            'largo_manga'      => 'getLargoManga',
                            'largo_pantalon'   => 'getLargoPantalon',
                        ];
                        foreach ($campos as $name => $label):
                            $valor = $ficha ? $ficha->{$getters[$name]}() : null;
                        ?>
                        <div class="form-group">
                            <label><?= $label ?></label>
                            <div class="input-cm">
                                <input type="number" name="<?= $name ?>"
                                       value="<?= $valor ?? '' ?>"
                                       placeholder="—" step="0.5" min="0">
                                <span class="cm-label">cm</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-footer">
                        <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-guardar">Guardar medidas</button>
                    </div>
                </form>
 
            <?php elseif ($ficha): ?>
                <!-- Mostrar medidas -->
                <div class="medidas-grid">
                    <?php
                    $medidas = [
                        'Contorno de Busto'    => $ficha->getContornoPecho(),
                        'Contorno de Cintura'  => $ficha->getContornoCintura(),
                        'Contorno de Cadera'   => $ficha->getContornoCadera(),
                        'Largo de Espalda'     => $ficha->getLargoEspalda(),
                        'Largo de Manga'       => $ficha->getLargoManga(),
                        'Largo de Pantalón'    => $ficha->getLargoPantalon(),
                    ];
                    foreach ($medidas as $label => $valor):
                        if ($valor === null) continue;
                    ?>
                    <div class="medida-card">
                        <div class="medida-label"><?= $label ?></div>
                        <div class="medida-valor">
                            <strong><?= number_format($valor, 0) ?></strong>
                            <span>cm</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sin-medidas">
                    Esta clienta todavía no tiene medidas registradas.<br>
                    <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1"
                       style="color:var(--marron)">Ingresar medidas</a>
                </div>
            <?php endif; ?>
        </div>
 
    </div>
 
    <!-- ── COLUMNA DERECHA ── -->
    <div class="panel-derecho">
 
        <!-- Encargos -->
        <div class="card">
            <div class="encargos-header">
                <div class="encargos-titulo">Encargos</div>
                <a href="<?= BASE_URL ?>/index.php?page=crear&cliente_id=<?= $cliente->getId() ?>"
                   class="btn-nuevo-encargo">+</a>
            </div>
 
            <?php if (empty($encargos)): ?>
                <div class="sin-encargos">Sin encargos registrados</div>
            <?php else: ?>
                <?php foreach ($encargos as $e): ?>
                <div class="encargo-item">
                    <div class="encargo-tipo"><?= htmlspecialchars($e['tipo']) ?></div>
                    <div class="encargo-fecha"><?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?></div>
                    <div class="encargo-bottom">
                        <span class="encargo-monto">$<?= number_format($e['monto_total'], 0, ',', '.') ?></span>
                        <span class="estado-badge estado-<?= $e['estado'] ?>">
                            <?= match($e['estado']) {
                                'pendiente'  => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'listo'      => 'Listo',
                                'entregado'  => 'Entregado',
                                default      => $e['estado']
                            } ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
 
        <!-- Resumen -->
        <div class="card">
            <div class="card-titulo">Resumen</div>
            <div class="resumen-fila">
                <span class="resumen-label">Total encargos</span>
                <span class="resumen-valor"><?= $totalEncargos ?></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Activos</span>
                <span class="resumen-valor"><?= $activos ?></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Saldo pendiente</span>
                <span class="resumen-valor pendiente">$<?= number_format($saldoPendiente, 0, ',', '.') ?></span>
            </div>
        </div>
 
    </div>
</div>