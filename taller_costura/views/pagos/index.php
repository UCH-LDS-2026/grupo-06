<?php
require_once BASE_PATH . '/models/Encargo.php';
$encargoModel = new Encargo();
$encargos = $encargoModel->obtenerTodos() ?? [];
?>

<div class="pagos-container">
    <div class="pagos-header">
        <h2>💰 Gestión de Pagos</h2>
    </div>

    <?php if (empty($encargos)): ?>
        <p class="sin-resultados">No hay encargos registrados.</p>
    <?php else: ?>
        <table class="tabla-pagos">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Descripción</th>
                    <th>Total</th>
                    <th>Seña</th>
                    <th>Saldo pendiente</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($encargos as $encargo): ?>
                <tr>
                    <td><?= htmlspecialchars($encargo['nombre_cliente'] ?? 'Sin cliente') ?></td>
                    <td><?= htmlspecialchars($encargo['descripcion']) ?></td>
                    <td>$<?= number_format($encargo['monto_total'], 2) ?></td>
                    <td>$<?= number_format($encargo['sena'], 2) ?></td>
                    <td>$<?= number_format($encargo['monto_total'] - $encargo['sena'], 2) ?></td>
                    <td>
                        <span class="estado estado-<?= $encargo['estado'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $encargo['estado'])) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>