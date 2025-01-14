<?php
require_once 'database.php'; // Inclure le fichier pour la connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $famille = $_POST['famille'];
    $nom_produit = $_POST['nom_produit'];
    $quantity = intval($_POST['quantity']);

    // Vérification des champs obligatoires
    if (empty($famille) || empty($nom_produit) || $quantity <= 0) {
        echo json_encode(["error" => "Tous les champs sont requis et la quantité doit être valide."]);
        exit;
    }

    try {
        // Vérifier si le produit existe déjà
        $checkStmt = $pdo->prepare("SELECT id, code_type, commercialise, MAX(barcode) AS last_barcode 
                                    FROM products 
                                    WHERE famille = :famille AND nom_produit = :nom_produit");
        $checkStmt->execute([
            ':famille' => $famille,
            ':nom_produit' => $nom_produit
        ]);
        $existingProduct = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProduct && $existingProduct['code_type']) {
            // Le produit existe déjà : vérifier si le code_type est commercialisé
            if ($existingProduct['commercialise'] == 0) {
                // Si commercialise est 0, empêcher l'ajout
                echo json_encode(["error" => "Impossible d'enregistrer ce produit. La gamme (code_type) n'est plus commercialisée."]);
                exit;
            }

            // Le produit est commercialisé : incrémenter le barcode
            $currentCodeType = $existingProduct['code_type'];
            $lastBarcode = $existingProduct['last_barcode'] ?? 0;

            // Préparer la requête pour insérer un produit avec le même code_type
            $stmt = $pdo->prepare("INSERT INTO products (famille, nom_produit, code_type, barcode) VALUES (:famille, :nom_produit, :code_type, :barcode)");

            for ($i = 0; $i < $quantity; $i++) {
                $stmt->execute([
                    ':famille' => $famille,
                    ':nom_produit' => $nom_produit,
                    ':code_type' => $currentCodeType,
                    ':barcode' => ++$lastBarcode
                ]);
            }

            echo json_encode(["success" => "$quantity enregistrements ajoutés pour le produit existant avec code_type $currentCodeType."]);
        } else {
            // Le produit n'existe pas : créer un nouveau code_type et commencer le barcode à 1
            $codeStmt = $pdo->query("SELECT MAX(code_type) AS last_code_type FROM products");
            $lastCodeType = $codeStmt->fetch(PDO::FETCH_ASSOC)['last_code_type'];
            $newCodeType = $lastCodeType ? $lastCodeType + 1 : 1015;
            $newBarcode = 1;

            // Préparer la requête pour insérer un nouveau produit
            $stmt = $pdo->prepare("INSERT INTO products (famille, nom_produit, code_type, barcode) VALUES (:famille, :nom_produit, :code_type, :barcode)");

            for ($i = 0; $i < $quantity; $i++) {
                $stmt->execute([
                    ':famille' => $famille,
                    ':nom_produit' => $nom_produit,
                    ':code_type' => $newCodeType,
                    ':barcode' => $newBarcode++
                ]);
            }

            echo json_encode(["success" => "Nouveau produit ajouté $quantity fois avec code_type $newCodeType."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur lors de l'insertion : " . $e->getMessage()]);
    }
}
