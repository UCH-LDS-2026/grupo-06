<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Cliente.php';
require_once __DIR__ . '/../../models/FichaCliente.php';
require_once __DIR__ . '/../../models/Encargo.php';
require_once __DIR__ . '/../../config/database.php';

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

$db      = Database::getInstance()->getConnection();
$encargo = new Encargo($db);
$stmt    = $encargo->getByClienteId($clienteId);
$encargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalEncargos  = count($encargos);
$activos        = count(array_filter($encargos, fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso'])));

$saldoPendiente = array_sum(array_map(
    fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso', 'listo'])
        ? $e['monto_total'] - $e['sena']
        : 0,
    $encargos
));

$exito = $_SESSION['exito_cliente'] ?? null;
$error = $_SESSION['error_cliente'] ?? null;
unset($_SESSION['exito_cliente'], $_SESSION['error_cliente']);

$modoEdicion      = isset($_GET['editar'])         && $_GET['editar']         === '1';
$modoEdicionDatos = isset($_GET['editar_cliente']) && $_GET['editar_cliente'] === '1';

function waLink(string $telefono, string $mensaje): string {
    $tel = preg_replace('/[^0-9]/', '', $telefono);
    $tel = ltrim($tel, '0');
    if (!str_starts_with($tel, '54')) {
        $tel = '54' . $tel;
    }
    return 'https://wa.me/' . $tel . '?text=' . rawurlencode($mensaje);
}

$telefono    = $cliente->getTelefono();
$nombre      = $cliente->getNombre();
$tieneTel    = $telefono !== '';

$msgSaludo   = "Hola {$nombre}! 👋 Te escribimos desde el taller. ¿Cómo estás?";
$msgEncargo  = "Hola {$nombre}! 🎉 Te avisamos que tu encargo está listo para retirar. ¡Podés pasar cuando quieras en nuestro horario habitual!";
$msgPago     = "Hola {$nombre}! 💳 Te recordamos que tenés un saldo pendiente de $" . number_format($saldoPendiente, 0, ',', '.') . ". Coordinamos el pago cuando quieras.";
$msgTurno    = "Hola {$nombre}! 📅 ¿Querés coordinar un turno para una prueba o medición? Avisanos y buscamos el horario que mejor te quede.";
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/fichaCliente.css">

<style>
/* ── WHATSAPP ─────────────────────────────────────────────── */
.wa-section {
    background: var(--bg-card);
    border: 1px solid var(--borde);
    border-radius: var(--r-xl);
    padding: 22px 24px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-card);
}

.wa-section-titulo {
    font-family: var(--serif);
    font-size: 1rem;
    font-weight: 400;
    color: var(--texto-pri);
    letter-spacing: .3px;
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 9px;
}

.wa-section-titulo .wa-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(37, 211, 102, 0.13);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.wa-section-titulo .wa-icon-wrap svg {
    width: 16px;
    height: 16px;
    fill: #25D366;
}

.wa-botones {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-wa {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: var(--bg);
    border: 1px solid var(--borde);
    border-radius: var(--r-md);
    font-family: var(--sans);
    font-size: .83rem;
    color: var(--texto-sec);
    text-decoration: none;
    cursor: pointer;
    transition: all .2s var(--ease);
    width: 100%;
    box-sizing: border-box;
}

.btn-wa:hover {
    background: var(--bg-hover);
    border-color: var(--borde-hover);
    color: var(--texto-pri);
    transform: translateX(2px);
}

.btn-wa .wa-emoji { font-size: 16px; flex-shrink: 0; }
.btn-wa .wa-texto { flex: 1; text-align: left; }
.btn-wa .wa-arrow {
    color: var(--texto-mute);
    font-size: .75rem;
    flex-shrink: 0;
    transition: transform .2s var(--ease);
}

.btn-wa:hover .wa-arrow {
    transform: translateX(3px);
    color: #25D366;
}

.wa-sin-tel {
    font-size: .8rem;
    color: var(--texto-ter);
    background: var(--bg);
    border: 1px dashed var(--borde);
    border-radius: var(--r-md);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.wa-sin-tel a {
    color: var(--acento-2);
    font-weight: 500;
    text-decoration: none;
    border-bottom: .5px solid var(--acento-2);
}

.btn-wa-principal {
    background: #25D366;
    border-color: #25D366;
    color: #ffffff;
    font-weight: 500;
    box-shadow: 0 3px 12px rgba(37,211,102,.3);
}
.btn-wa-principal:hover {
    background: #22c55e;
    border-color: #22c55e;
    color: #ffffff;
    box-shadow: 0 5px 16px rgba(37,211,102,.4);
}
.btn-wa-principal .wa-arrow { color: rgba(255,255,255,.7); }
.btn-wa-principal:hover .wa-arrow { color: #ffffff; }
</style>

<?php if ($exito): ?>
    <div class="alerta alerta-ok"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta alerta-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php $origenFicha = $_GET['origen'] ?? 'clientes'; ?>
<a href="<?= $origenFicha === 'pagos' ? BASE_URL . '/index.php?page=pagos&tab=historial' : BASE_URL . '/index.php?page=clientes' ?>" class="volver">
    ← Volver a <?= $origenFicha === 'pagos' ? 'Pagos' : 'Clientes' ?>
</a>

<h1 class="cliente-titulo"><?= htmlspecialchars($cliente->getNombre()) ?></h1>

<div class="ficha-layout">

    <!-- ── COLUMNA IZQUIERDA ── -->
    <div>

        <!-- Información de Contacto -->
        <div class="card">
            <div class="card-titulo">Información de Contacto</div>

            <?php if ($modoEdicionDatos): ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id" value="<?= $cliente->getId() ?>">
                    <div class="form-group" style="margin-bottom:12px">
                        <label>Nombre completo <span class="req">*</span></label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($cliente->getNombre()) ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono"
                               value="<?= htmlspecialchars($cliente->getTelefono() ?: '') ?>"
                               maxlength="15"
                               pattern="[0-9]{7,15}"
                               title="Solo números, entre 7 y 15 dígitos"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group" style="margin-bottom:12px">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($cliente->getEmail() ?: '') ?>">
                    </div>
                    <div class="form-footer">
                        <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-guardar">Guardar cambios</button>
                    </div>
                </form>

            <?php else: ?>
                <div class="contacto-lista">

                    <div class="contacto-item">
                        <div class="contacto-icono">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 5.25 13 19.79 19.79 0 0 1 2.18 4.35 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="contacto-label">Teléfono</div>
                            <div class="contacto-valor"><?= htmlspecialchars($cliente->getTelefono() ?: '—') ?></div>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <div class="contacto-icono">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m2 7 10 7 10-7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="contacto-label">Email</div>
                            <div class="contacto-valor"><?= htmlspecialchars($cliente->getEmail() ?: '—') ?></div>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <div class="contacto-icono">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                        </div>
                        <div>
                            <div class="contacto-label">Cliente desde</div>
                            <div class="contacto-valor">
                                <?= date('d \d\e F \d\e Y', strtotime($cliente->getCreatedAt())) ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="acciones-cliente" style="margin-top:20px; margin-bottom:0">
                    <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar_cliente=1"
                       class="btn-accion btn-accion-editar">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/>
                        </svg>
                        Editar datos
                    </a>
                    <form method="POST" action="<?= BASE_URL ?>/index.php"
                          onsubmit="return confirm('¿Eliminar esta clienta? Esta acción no se puede deshacer.')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $cliente->getId() ?>">
                        <button type="submit" class="btn-accion btn-accion-eliminar">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Eliminar clienta
                        </button>
                    </form>
                </div>

            <?php endif; ?>
        </div>

        <!-- ── WHATSAPP ── -->
        <div class="wa-section">
            <div class="wa-section-titulo">
                <div class="wa-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                    </svg>
                </div>
                WhatsApp
            </div>

            <?php if ($tieneTel): ?>
                <div class="wa-botones">
                    <a href="<?= waLink($telefono, $msgSaludo) ?>"
                       target="_blank" rel="noopener"
                       class="btn-wa btn-wa-principal">
                        <span class="wa-emoji">💬</span>
                        <span class="wa-texto">Enviar mensaje</span>
                        <span class="wa-arrow">→</span>
                    </a>
                    <a href="<?= waLink($telefono, $msgEncargo) ?>"
                       target="_blank" rel="noopener"
                       class="btn-wa">
                        <span class="wa-emoji">🎉</span>
                        <span class="wa-texto">Encargo listo para retirar</span>
                        <span class="wa-arrow">→</span>
                    </a>
                    <a href="<?= waLink($telefono, $msgPago) ?>"
                       target="_blank" rel="noopener"
                       class="btn-wa">
                        <span class="wa-emoji">💳</span>
                        <span class="wa-texto">Recordar saldo pendiente</span>
                        <span class="wa-arrow">→</span>
                    </a>
                    <a href="<?= waLink($telefono, $msgTurno) ?>"
                       target="_blank" rel="noopener"
                       class="btn-wa">
                        <span class="wa-emoji">📅</span>
                        <span class="wa-texto">Coordinar turno / prueba</span>
                        <span class="wa-arrow">→</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="wa-sin-tel">
                    <span>⚠️</span>
                    Esta clienta no tiene teléfono registrado.
                    <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar_cliente=1">
                        Agregar ahora
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Medidas -->
        <div class="card">
            <div class="medidas-header">
                <div class="medidas-titulo">
                    <div class="medidas-icono-wrap">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6h18M3 12h18M3 18h18"/>
                            <path d="M7 3v3M12 3v3M17 3v3"/>
                        </svg>
                    </div>
                    Medidas Guardadas
                </div>
                <?php if (!$modoEdicion): ?>
                    <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1"
                       class="btn-editar-medidas">Editar medidas</a>
                <?php endif; ?>
            </div>

            <?php if ($modoEdicion): ?>
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
                                       placeholder="—" step="0.5" min="1" max="300">
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
                <div class="medidas-grid">
                    <?php
                    $medidas = [
                        'Contorno de Busto'   => $ficha->getContornoPecho(),
                        'Contorno de Cintura' => $ficha->getContornoCintura(),
                        'Contorno de Cadera'  => $ficha->getContornoCadera(),
                        'Largo de Espalda'    => $ficha->getLargoEspalda(),
                        'Largo de Manga'      => $ficha->getLargoManga(),
                        'Largo de Pantalón'   => $ficha->getLargoPantalon(),
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
                    <a href="?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1">Ingresar medidas</a>
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
                   class="btn-nuevo-encargo">+ Nuevo</a>
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