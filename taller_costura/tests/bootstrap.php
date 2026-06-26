<?php
// tests/bootstrap.php
// Carga las clases del sistema sin arrancar la aplicación completa

define('BASE_PATH', 'C:/wamp64/www/sistema_costura/grupo-06/taller_costura');

require_once BASE_PATH . '/vendor/autoload.php';

require_once BASE_PATH . '/models/Cliente.php';
require_once BASE_PATH . '/models/Encargo.php';