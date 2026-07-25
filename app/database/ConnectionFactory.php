<?php

namespace app\database;

use PDO;
use PDOException;

/**
 * Fábrica de conexões PDO (Singleton).
 * Única classe autorizada a criar a conexão com o MySQL.
 */
class ConnectionFactory
{
    private static ?PDO $instance = null;

    /** Impede instanciação direta — use ConnectionFactory::getConnection(). */
    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = self::createPdo();
            } catch (PDOException $e) {
                if (self::isUnknownDatabase($e)) {
                    \app\database\DatabaseInitializer::initialize();
                    self::$instance = self::createPdo();

                    return self::$instance;
                }

                if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
                    throw $e;
                }

                http_response_code(500);
                exit('Erro ao conectar ao banco de dados.');
            }
        }

        return self::$instance;
    }

    private static function createPdo(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_NAME
        );

        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private static function isUnknownDatabase(PDOException $exception): bool
    {
        $errorInfo = $exception->errorInfo ?? [];
        $mysqlErrorCode = $errorInfo[1] ?? null;

        return $mysqlErrorCode === 1049
            || str_contains($exception->getMessage(), 'Unknown database')
            || str_contains($exception->getMessage(), '1049');
    }
}