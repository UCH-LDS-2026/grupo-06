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
$saldoPendiente = array_sum(array_map(fn($e) => $e['monto_total'] - $e['sena'], $encargos));

$exito = $_SESSION['exito_cliente'] ?? null;
$error = $_SESSION['error_cliente'] ?? null;
unset($_SESSION['exito_cliente'], $_SESSION['error_cliente']);

$modoEdicion      = isset($_GET['editar'])         && $_GET['editar']         === '1';
$modoEdicionDatos = isset($_GET['editar_cliente']) && $_GET['editar_cliente'] === '1';
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/fichaCliente.css">

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
                        <input type="text" name="telefono" value="<?= htmlspecialchars($cliente->getTelefono() ?: '') ?>">
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

                    <!-- Teléfono -->
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

                    <!-- Email -->
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

                    <!-- Fecha -->
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

                <!-- Botones editar / eliminar -->
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

        <!-- Medidas -->
        <div class="card">
            <div class="medidas-header">
                <div class="medidas-titulo">
                    <!-- Burbuja circular pastel igual al dashboard -->
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