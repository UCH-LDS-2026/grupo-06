<?php
/**
 * Archivo de configuración de la aplicación
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_costura');

// Configuración general
define('APP_NAME', 'Sistema de Costura');
define('APP_VERSION', '1.0.0');

// Rutas
define('BASE_PATH', dirname(dirname(__FILE__)));
define('VIEWS_PATH', BASE_PATH . '/views');
define('MODELS_PATH', BASE_PATH . '/models');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// URL base — Siempre consistente basado en BASE_PATH
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtener DOCUMENT_ROOT sin barras al final
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    
    // Obtener BASE_PATH convertido
    $basePath = str_replace('\\', '/', BASE_PATH);
    
    // Calcular la ruta relativa desde DOCUMENT_ROOT
    if (strpos($basePath, $docRoot) === 0) {
        $urlPath = substr($basePath, strlen($docRoot));
    } else {
        $urlPath = '/' . basename($basePath);
    }
    
    // Asegurar que comience con /
    if (strpos($urlPath, '/') !== 0) {
        $urlPath = '/' . $urlPath;
    }
    
    define('BASE_URL', $protocol . $host . $urlPath);
}
?>
