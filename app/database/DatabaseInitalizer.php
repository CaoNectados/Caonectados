<?php

namespace app\database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Inicializador do banco de dados.
 * Cria o schema (se não existir) e executa o scripts.sql.
 *
 * IMPORTANTE: o arquivo antigo "DatabaseInitalizer.php" (com typo) deve ser
 * removido do repositório — o Autoload exige que nome do arquivo == nome da classe.
 */
class DatabaseInitializer
{
    public static function initialize(): void
    {
        $dsn = sprintf('mysql:host=%s;charset=utf8mb4', DB_HOST);

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
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
