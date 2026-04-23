<?php

namespace App\Core;

/**
 * Database — Singleton PDO MySQL connection
 * Design Pattern: Singleton
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    private function __construct(array $dbConfig)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['name']
        );

        $this->pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(array $dbConfig): self
    {
        if (self::$instance === null) {
            self::$instance = new self($dbConfig);
        }
        return self::$instance;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /** Prevent cloning */
    private function __clone() {}
}
