<?php
require_once '../lib/database.php';
$pdo = getPDOConnection();
var_dump($pdo);

try {
    $stmt = $pdo->query('SELECT * FROM products');
    $produits = $stmt->fetchAll();
    echo "<h2>Liste des produits :</h2>";
    echo "<ul>";
    foreach ($produits as $produit) {
        echo "<li>" . $produit['title'] . " - " 
                    . $produit['category'] .  " - " 
                    . $produit['price'] . "€ - "
                    . $produit['location'] . " - "
                    . $produit['image']
                    . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
