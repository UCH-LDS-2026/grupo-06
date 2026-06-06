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

// URL base — funciona para cualquier nombre de carpeta raíz
define('BASE_URL', '//' . $_SERVER['HTTP_HOST'] . str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', BASE_PATH)
));
?>
