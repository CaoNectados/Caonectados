<?php

// Configuração do ambiente
define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração do Sistema
define('APP_NAME', 'CaoNectados');
define('BASE_URL', 'http://localhost/ModeloProjetoIntegrador/public');
define('URL_BASE', BASE_URL);

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'caonectados');
define('DB_USER', 'root');
define('DB_PASS', '');

