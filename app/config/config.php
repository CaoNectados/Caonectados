<?php

// Configuração do ambiente
define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração do Sistema
define('APP_NAME', 'CaoNectados');
define('URL_BASE', 'http://localhost/ModeloProjetoIntegrador/public');
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'caonectados');
define('DB_USER', 'root');
define('DB_PASS', 'root');


