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

$db       = Database::getInstance()->getConnection();
$encargo = new Encargo($db);
$stmt     = $encargo->getByClienteId($clienteId);
$encargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalEncargos  = count($encargos);
$activos        = count(array_filter($encargos, fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso'])));
$saldoPendiente = array_sum(array_map(fn($e) => $e['monto_total'] - $e['sena'], $encargos));

$exito = $_SESSION['exito_cliente'] ?? null;
$error = $_SESSION['error_cliente'] ?? null;
unset($_SESSION['exito_cliente'], $_SESSION['error_cliente']);

$modoEdicion      = isset($_GET['editar'])         && $_GET['editar']        === '1';
$modoEdicionDatos = isset($_GET['editar_cliente']) && $_GET['editar_cliente'] === '1';

// Iniciales para el avatar
$nombre    = trim($cliente->getNombre());
$partes    = explode(' ', $nombre);
$iniciales = implode('', array_map(fn($p) => !empty($p) ? strtoupper($p[0]) : '', array_slice($partes, 0, 2)));
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/fichaCliente.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<?php if ($exito): ?>
    <div class="alerta alerta-ok"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta alerta-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<a href="<?= BASE_URL ?>/index.php?page=clientes" class="volver">← Volver a Clientes</a>

<div class="cliente-header">
    <div class="cliente-avatar-grande"><?= htmlspecialchars($iniciales) ?></div>
    <h1 class="cliente-titulo"><?= htmlspecialchars($cliente->getNombre()) ?></h1>
</div>
<p class="cliente-subtitulo">Ficha de cliente</p>

<div class="ficha-layout">
    <div>
        <div class="card">
            <div class="card-titulo">
                <span class="material-symbols-outlined">person</span>
                Información de Contacto
            </div>

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
                        <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-guardar">Guardar cambios</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="contacto-lista">
                    <div class="contacto-item">
                        <div class="contacto-icono"><span class="material-symbols-outlined">call</span></div>
                        <div>
                            <div class="contacto-label">Teléfono</div>
                            <div class="contacto-valor"><?= htmlspecialchars($cliente->getTelefono() ?: '—') ?></div>
                        </div>
                    </div>
                    <div class="contacto-item">
                        <div class="contacto-icono"><span class="material-symbols-outlined">mail</span></div>
                        <div>
                            <div class="contacto-label">Email</div>
                            <div class="contacto-valor"><?= htmlspecialchars($cliente->getEmail() ?: '—') ?></div>
                        </div>
                    </div>
                    <div class="contacto-item">
                        <div class="contacto-icono"><span class="material-symbols-outlined">calendar_month</span></div>
                        <div>
                            <div class="contacto-label">Cliente desde</div>
                            <div class="contacto-valor"><?= date('d \d\e F \d\e Y', strtotime($cliente->getCreatedAt())) ?></div>
                        </div>
                    </div>
                </div>

                <div class="acciones-cliente">
                    <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar_cliente=1" class="btn-accion btn-accion-editar">
                        <span class="material-symbols-outlined">edit</span> Editar datos
                    </a>
                    <form method="POST" action="<?= BASE_URL ?>/index.php" onsubmit="return confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $cliente->getId() ?>">
                        <button type="submit" class="btn-accion btn-accion-eliminar">
                            <span class="material-symbols-outlined">delete</span> Eliminar cliente
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="medidas-header">
                <div class="medidas-titulo">
                    <span class="material-symbols-outlined">square_foot</span>
                    Medidas Guardadas
                </div>
                <?php if (!$modoEdicion): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1" class="btn-editar-medidas">
                        <span class="material-symbols-outlined">edit</span> Editar medidas
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($modoEdicion): ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php">
                    <input type="hidden" name="accion" value="guardar_ficha">
                    <input type="hidden" name="cliente_id" value="<?= $cliente->getId() ?>">
                    <div class="form-medidas-grid">
                        <?php
                        $campos = ['contorno_pecho' => 'Contorno de Busto', 'contorno_cintura' => 'Contorno de Cintura', 'contorno_cadera' => 'Contorno de Cadera', 'largo_espalda' => 'Largo de Espalda', 'largo_manga' => 'Largo de Manga', 'largo_pantalon' => 'Largo de Pantalón'];
                        $getters = ['contorno_pecho' => 'getContornoPecho', 'contorno_cintura' => 'getContornoCintura', 'contorno_cadera' => 'getContornoCadera', 'largo_espalda' => 'getLargoEspalda', 'largo_manga' => 'getLargoManga', 'largo_pantalon' => 'getLargoPantalon'];
                        foreach ($campos as $name => $label):
                            $valor = $ficha ? $ficha->{$getters[$name]}() : null;
                        ?>
                        <div class="form-group">
                            <label><?= $label ?></label>
                            <div class="input-cm">
                                <input type="number" name="<?= $name ?>" value="<?= $valor ?? '' ?>" placeholder="—" step="0.5" min="0">
                                <span class="cm-label">cm</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-footer">
                        <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-guardar">Guardar medidas</button>
                    </div>
                </form>

            <?php elseif ($ficha): ?>
                <div class="medidas-grid">
                    <?php
                    $medidas = ['Contorno de Busto' => $ficha->getContornoPecho(), 'Contorno de Cintura' => $ficha->getContornoCintura(), 'Contorno de Cadera' => $ficha->getContornoCadera(), 'Largo de Espalda' => $ficha->getLargoEspalda(), 'Largo de Manga' => $ficha->getLargoManga(), 'Largo de Pantalón' => $ficha->getLargoPantalon()];
                    foreach ($medidas as $label => $valor):
                        if ($valor === null) continue;
                    ?>
                    <div class="medida-card">
                        <div class="medida-label"><?= $label ?></div>
                        <div class="medida-valor"><strong><?= number_format($valor, 0) ?></strong> <span>cm</span></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sin-medidas">
                    Este cliente todavía no tiene medidas registradas.<br>
                    <a href="<?= BASE_URL ?>/index.php?page=ficha-cliente&id=<?= $cliente->getId() ?>&editar=1">Ingresar medidas</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel-derecho">
        <div class="card">
            <div class="encargos-header">
                <div class="encargos-titulo">Encargos</div>
                <a href="<?= BASE_URL ?>/index.php?page=crear&cliente_id=<?= $cliente->getId() ?>" class="btn-nuevo-encargo">+</a>
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

        <div class="card">
            <div class="card-titulo">Resumen</div>
            <div class="resumen-fila"><span class="resumen-label">Total encargos</span><span class="resumen-valor"><?= $totalEncargos ?></span></div>
            <div class="resumen-fila"><span class="resumen-label">Activos</span><span class="resumen-valor"><?= $activos ?></span></div>
            <div class="resumen-fila"><span class="resumen-label">Saldo pendiente</span><span class="resumen-valor pendiente">$<?= number_format($saldoPendiente, 0, ',', '.') ?></span></div>
        </div>
    </div>
</div>