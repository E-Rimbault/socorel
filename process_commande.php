<?php
// Inclure le fichier de configuration de la base de données
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nomCommande = $_POST['nom_commande'] ?? '';
    $nomDestinataire = $_POST['destinataire_commande'] ?? ''; // Correspond au champ "nom_destinataire"
    $produits = json_decode($_POST['produits'], true) ?? [];

    // Validation des champs obligatoires
    if (empty($nomCommande) || empty($nomDestinataire) || empty($produits)) {
        die('Tous les champs sont obligatoires. Veuillez vérifier les informations fournies.');
    }

    try {
        $pdo->beginTransaction();
        $produitsIntrouvables = []; // Liste des produits non trouvés ou insuffisants

        foreach ($produits as $produit) {
            if (!isset($produit['code'], $produit['quantite'])) {
                throw new Exception('Données produit manquantes : ' . json_encode($produit));
            }

            $codeType = $produit['code'];
            $quantiteSouhaitee = (int)$produit['quantite'];

            if ($quantiteSouhaitee <= 0) {
                throw new Exception("Quantité invalide pour le produit avec code_type $codeType");
            }

            // Récupérer les produits en fonction de la quantité souhaitée
            $sqlProduit = "SELECT id, nom_produit, famille, code_type, barcode, created_at 
                           FROM products 
                           WHERE code_type = ? AND en_stock = 'stocker'
                           ORDER BY created_at ASC 
                           LIMIT $quantiteSouhaitee";
            $stmtProduit = $pdo->prepare($sqlProduit);
            $stmtProduit->execute([$codeType]);
            $detailsProduits = $stmtProduit->fetchAll(PDO::FETCH_ASSOC);

            // Vérifier si suffisamment de produits sont disponibles
            if (count($detailsProduits) < $quantiteSouhaitee) {
                $produitsIntrouvables[] = [
                    'code' => $codeType,
                    'quantite_demandee' => $quantiteSouhaitee,
                    'quantite_disponible' => count($detailsProduits),
                ];
                continue; // Ignorer ce produit et continuer avec les autres
            }

            // Insérer les produits dans le bon de commande et mettre à jour leur état
            foreach ($detailsProduits as $detailsProduit) {
                // Insertion dans la table bon_de_commande
                $sqlCommande = "INSERT INTO bon_de_commande (nom_commande, nom_produit, famille, code_type, barcode, destinataire, etat_commande) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtCommande = $pdo->prepare($sqlCommande);
                $stmtCommande->execute([
                    $nomCommande,
                    $detailsProduit['nom_produit'],
                    $detailsProduit['famille'],
                    $detailsProduit['code_type'],
                    $detailsProduit['barcode'],
                    $nomDestinataire, // Enregistrement explicite du nom du destinataire
                    'complet',
                ]);

                // Mise à jour de l'état dans la table products
                $sqlUpdateProduit = "UPDATE products SET en_stock = 'envoyé' WHERE id = ?";
                $stmtUpdateProduit = $pdo->prepare($sqlUpdateProduit);
                $stmtUpdateProduit->execute([$detailsProduit['id']]);
            }
        }

        // Si des produits étaient introuvables ou insuffisants
        if (!empty($produitsIntrouvables)) {
            $messagesErreurs = array_map(function ($produit) {
                return "Code_type: {$produit['code']}, Quantité demandée: {$produit['quantite_demandee']}, Quantité disponible: {$produit['quantite_disponible']}";
            }, $produitsIntrouvables);

            throw new Exception('Certains produits sont introuvables ou insuffisants : ' . implode(' | ', $messagesErreurs));
        }

        $pdo->commit();
        echo 'Bon de commande enregistré avec succès pour le destinataire : ' . htmlspecialchars($nomDestinataire);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
    }
} else {
    echo 'Méthode non autorisée.';
}
