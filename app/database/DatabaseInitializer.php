<?php

namespace app\database;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseInitializer
{
    public static function initialize(): void
    {
        $dsn = sprintf('mysql:host=%s;charset=utf8mb4', DB_HOST);

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` '
                . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
            $pdo->exec('USE `' . DB_NAME . '`');

            $scriptPath = __DIR__ . '/scripts/scripts.sql';

            if (!file_exists($scriptPath)) {
                throw new RuntimeException("Script SQL não encontrado em: {$scriptPath}");
            }

            $sql = file_get_contents($scriptPath);

            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('Script SQL vazio ou ilegível.');
            }

            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
                throw $e;
            }

            http_response_code(500);
            exit('Erro ao inicializar o banco de dados.');
        }
    }
}