<?php

namespace app\core;

use app\database\ConnectionFactory;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? ConnectionFactory::getConnection();
    }
}