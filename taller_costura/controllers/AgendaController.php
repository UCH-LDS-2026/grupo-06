<?php
require_once BASE_PATH . '/models/Encargo.php';
require_once BASE_PATH . '/models/Observacion.php';

class AgendaController {
    private $encargo;
    private $observacion;

    public function __construct($db) {
        $this->encargo     = new Encargo($db);
        $this->observacion = new Observacion($db);
    }

    /**
     * Devuelve datos para la vista agenda:
     * - stats: conteos y saldo pendiente
     * - proximas: encargos no entregados ordenados por fecha_entrega ASC
     * - recientes: últimos 5 entregados
     * Cada encargo incluye sus observaciones y días de diferencia.
     */
    public function getDatosAgenda(): array {
        $todos = $this->encargo->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $hoy   = new DateTime();

        $activos   = 0;
        $enProceso = 0;
        $listos    = 0;
        $saldoPend = 0;
        $proximas  = [];
        $recientes = [];

        foreach ($todos as $e) {
            $fechaEntrega = new DateTime($e['fecha_entrega']);
            $diff = (int)$hoy->diff($fechaEntrega)->days;
            $pasado = $fechaEntrega < $hoy;

            $e['dias_diff']  = $pasado ? -$diff : $diff;
            $e['saldo']      = $e['monto_total'] - $e['sena'];
            $e['observaciones'] = $this->observacion->getByEncargo($e['id']);

            if ($e['estado'] !== 'entregado') {
                $activos++;
                $saldoPend += $e['saldo'];
                if ($e['estado'] === 'en_proceso') $enProceso++;
                if ($e['estado'] === 'listo')      $listos++;
                $proximas[] = $e;
            } else {
                $recientes[] = $e;
            }
        }

        // recientes: los 5 más recientes por fecha_entrega desc
        usort($recientes, fn($a,$b) => strcmp($b['fecha_entrega'], $a['fecha_entrega']));
        $recientes = array_slice($recientes, 0, 5);

        return [
            'stats'     => ['activos' => $activos, 'en_proceso' => $enProceso, 'listos' => $listos, 'saldo_pendiente' => $saldoPend],
            'proximas'  => $proximas,
            'recientes' => $recientes,
        ];
    }
}
?>