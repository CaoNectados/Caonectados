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
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_NAME
            );

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // Lança exceções em erros de SQL (nunca falha silenciosamente)
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // fetch() retorna array associativo por padrão
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Prepared statements NATIVOS do MySQL (proteção real contra SQL Injection)
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
                    throw $e; // em desenvolvimento, mostra o erro real
                }
                http_response_code(500);
                exit('Erro ao conectar ao banco de dados.');
            }
        }

        return self::$instance;
    }
}
