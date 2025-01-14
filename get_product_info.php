<?php
include 'database.php';

if (isset($_GET['code_type'])) {
    $code_type = $_GET['code_type'];

    try {
        // Sélectionner le nom du produit et compter les produits en stock
        $sql = "SELECT nom_produit, COUNT(*) AS stock_count
                FROM products
                WHERE code_type = :code_type AND en_stock = 'stocker'
                GROUP BY nom_produit";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':code_type', $code_type);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product = [
                'nom_produit' => $row['nom_produit'],
                'stock_count' => $row['stock_count']  // Compte des produits en stock
            ];
            echo json_encode($product);
        } else {
            echo json_encode(null); // Aucun produit trouvé pour ce code_type
        }
    } catch (PDOException $e) {
        echo json_encode(null); // En cas d'erreur
    }
}
?>
