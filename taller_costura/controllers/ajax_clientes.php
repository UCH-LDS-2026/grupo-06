<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/FichaCliente.php';

header('Content-Type: application/json');

$adminId = $_SESSION['admin_id'] ?? null;
if (!$adminId) {
    echo json_encode(['ok' => false]);
    exit;
}

$busqueda = trim($_GET['buscar'] ?? '');
$filtro   = $_GET['filtro'] ?? 'todas';
$pagina   = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 8;

// Obtener todos
$todos = Cliente::getAll();

$conFicha = [];
$sinFicha = [];
foreach ($todos as $c) {
    $ficha = FichaCliente::getByClienteId($c->getId());
    if ($ficha !== null) $conFicha[] = $c;
    else $sinFicha[] = $c;
}

// Aplicar filtro/búsqueda
if ($busqueda !== '') {
    $clientes = Cliente::buscar($busqueda);
} elseif ($filtro === 'con_ficha') {
    $clientes = $conFicha;
} elseif ($filtro === 'sin_ficha') {
    $clientes = $sinFicha;
} else {
    $clientes = $todos;
}

// Paginación
$totalClientes = count($clientes);
$totalPaginas  = max(1, (int)ceil($totalClientes / $porPagina));
$pagina        = min($pagina, $totalPaginas);
$offset        = ($pagina - 1) * $porPagina;
$clientesPagina = array_slice($clientes, $offset, $porPagina);

// Construir cards HTML
$html = '';
if (empty($clientes)) {
    $html = '<div class="empty"><div class="empty-icon">👤</div><p>No hay clientas registradas todavía.</p></div>';
} else {
    $html .= '<div class="clientes-grid">';
    foreach ($clientesPagina as $c) {
        $ficha     = FichaCliente::getByClienteId($c->getId());
        $nombre    = trim($c->getNombre());
        $partes    = explode(' ', $nombre);
        $iniciales = implode('', array_map(fn($p) => !empty($p) ? strtoupper($p[0]) : '', array_slice($partes, 0, 2)));
        $tieneFicha = $ficha !== null;
        $desde     = date('M Y', strtotime($c->getCreatedAt()));
        $id        = $c->getId();

        $tagFicha = $tieneFicha
            ? '<span class="tag verde"><span class="material-symbols-outlined">check_circle</span> Con ficha</span>'
            : '<span class="tag naranja"><span class="material-symbols-outlined">disabled_by_default</span> Sin ficha</span>';

        $html .= "
        <div class='cliente-card'>
            <div class='card-header'>
                <div class='avatar-marco'>
                    <div class='avatar-iniciales'>" . htmlspecialchars($iniciales) . "</div>
                </div>
                <div class='cliente-nombre'>" . htmlspecialchars($c->getNombre()) . "</div>
            </div>
            <div class='cliente-datos'>
                <span class='dato'><span class='material-symbols-outlined'>call</span> " . htmlspecialchars($c->getTelefono()) . "</span>
                <span class='dato'><span class='material-symbols-outlined'>mail</span> " . htmlspecialchars($c->getEmail()) . "</span>
            </div>
            <div class='cliente-tags'>
                {$tagFicha}
                <span class='tag'><span class='material-symbols-outlined'>person</span> desde {$desde}</span>
            </div>
            <a href='?page=ficha-cliente&id={$id}' class='btn-perfil'>
                Ver perfil <span class='material-symbols-outlined'>chevron_right</span>
            </a>
        </div>";
    }
    $html .= '</div>';

    // Paginación
    if ($totalPaginas > 1) {
        $prev = $pagina > 1
            ? "<a href='#' class='pag-btn pag-prev' onclick='cambiarPagina({$pagina}-1);return false'>‹</a>"
            : "<span class='pag-btn pag-prev disabled'>‹</span>";
        $next = $pagina < $totalPaginas
            ? "<a href='#' class='pag-btn pag-next' onclick='cambiarPagina({$pagina}+1);return false'>›</a>"
            : "<span class='pag-btn pag-next disabled'>›</span>";
        $html .= "<div class='paginacion'>{$prev}<span class='pag-info'>Página {$pagina} de {$totalPaginas}</span>{$next}</div>";
    }
}

echo json_encode([
    'ok'           => true,
    'html'         => $html,
    'total'        => $totalClientes,
    'pagina'       => $pagina,
    'totalPaginas' => $totalPaginas,
    'sinFicha'     => count($sinFicha),
]);