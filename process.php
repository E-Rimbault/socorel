<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_produit = trim($_POST['nom_produit']);
    $famille = trim($_POST['famille']);

    // Validation côté serveur
    if (empty($nom_produit)) {
        die("Erreur : Le nom du produit est requis.");
    }

    if (empty($famille)) {
        die("Erreur : La famille du produit est requise.");
    }

    try {
        // Vérifier si la gamme est encore commercialisée
        $sql = "SELECT commercialise FROM products WHERE famille = :famille LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':famille' => $famille]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($result['commercialise'] == 0) {
                die("Erreur : Impossible d'ajouter un produit. La gamme '$famille' n'est plus commercialisée.");
            }
        }

        // Vérifier si le nom_produit existe déjà dans la base
        $sql = "SELECT code_type FROM products WHERE nom_produit = :nom_produit LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nom_produit' => $nom_produit]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProduct) {
            // Utiliser le code_type existant
            $code_type = $existingProduct['code_type'];
        } else {
            // Générer un nouveau code_type
            $sql = "SELECT MAX(code_type) + 1 AS new_code_type FROM products";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $code_type = $result['new_code_type'] ?? 1; // Si aucun produit n'existe encore, on commence à 1
        }

        // Insérer le produit dans la base
        $sql = "INSERT INTO products (nom_produit, famille, code_type, commercialise) VALUES (:nom_produit, :famille, :code_type, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom_produit' => $nom_produit,
            ':famille' => $famille,
            ':code_type' => $code_type,
        ]);

        echo "Produit ajouté avec succès. Code Type : $code_type";
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout du produit : " . $e->getMessage();
    }
} else {
    echo "Méthode de requête non autorisée.";
}
?>
