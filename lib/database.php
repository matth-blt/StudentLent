<?php
/**
* Ce fichier gère la connexion à la base de données.
*/
require_once __DIR__ . '../../config.php';
/**
* Retourne une connexion PDO à la base de données.
* Utilise un singleton pour éviter les connexions multiples.
*
* @return PDO
*/
function getPDOConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Erreur de connexion : ' . $e->getMessage());
            die('Erreur de connexion à la base de données.');
        }
    }
    return $pdo;
}