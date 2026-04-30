<?php

//Base de données pour OVH
require_once __DIR__ . '/config.php';

class Base
{
    private static ?PDO $pdo = null;

    public static function connexion(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=ecornezbrisingr.mysql.db;dbname=ecornezbrisingr;charset=utf8mb4';

            self::$pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}
