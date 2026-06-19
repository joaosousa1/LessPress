<?php
// app/core/Database.php

class Database 
{
    private static ?PDO $instance = null;

    /**
     * Connects to the database using an agnostic approach supporting SQLite, MySQL, and PostgreSQL
     * @param array $config Connection configuration array
     * @return PDO
     * @throws Exception If an unsupported driver is provided
     */
    public static function connect(array $config): PDO 
    {
        if (self::$instance === null) {
            try {
                $driver = $config['driver'] ?? '';

                if ($driver === 'sqlite') {
                    $dsn = "sqlite:" . $config['database'];
                    self::$instance = new PDO($dsn);
                } 
                elseif ($driver === 'mysql') {
                    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
                    // Dynamic port handling for MySQL if specified
                    if (!empty($config['port'])) {
                        $dsn .= ";port={$config['port']}";
                    }
                    self::$instance = new PDO($dsn, $config['username'], $config['password']);
                } 
                // Added full compliance support for PostgreSQL
                elseif ($driver === 'pgsql') {
                    $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";
                    if (!empty($config['port'])) {
                        $dsn .= ";port={$config['port']}";
                    }
                    self::$instance = new PDO($dsn, $config['username'], $config['password']);
                } 
                else {
                    throw new Exception("Database driver '{$driver}' is not supported by the core architecture.");
                }

                // Core security and execution attributes for PDO
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                die("Database connection failure: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}