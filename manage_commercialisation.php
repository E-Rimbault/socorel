<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_type = intval($_POST['code_type']);
    $status = intval($_POST['status']);
    $message = "";

    if ($code_type <= 0 || ($status !== 0 && $status !== 1)) {
        $message = "Entrées invalides. Veuillez réessayer.";
    } else {
        try {
            // Mettre à jour le statut de commercialisation
            $updateQuery = "UPDATE products SET commercialise = :status WHERE code_type = :code_type";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute(['status' => $status, 'code_type' => $code_type]);

            if ($status === 0) {
                // Supprimer les produits en attente (sans barcode) si la gamme est non commercialisée
                $deleteQuery = "DELETE FROM products WHERE code_type = :code_type AND barcode IS NULL";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->execute(['code_type' => $code_type]);
            }

            $message = "Statut de commercialisation mis à jour avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
    
    // Générer le JavaScript pour afficher la pop-up
    echo "<script type='text/javascript'>
            alert('" . addslashes($message) . "');
            window.location.href = document.referrer; // Retourner à la page précédente
          </script>";
    exit;
} else {
    echo "<script type='text/javascript'>
            alert('Méthode non autorisée.');
            window.location.href = document.referrer; // Retourner à la page précédente
          </script>";
    exit;
}
?>
