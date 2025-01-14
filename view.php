<?php
require 'database.php';

try {
    // Requête principale pour récupérer les produits triés par barcode numérique
    $sql = "
        SELECT 
            p.id, 
            p.nom_produit, 
            p.famille, 
            p.code_type, 
            p.barcode, 
            p.created_at, 
            p.date_envoi, 
            p.commercialise, 
            p.en_stock,
            b.nom_commande AS reference_commande, 
            b.destinataire
        FROM 
            products p
        LEFT JOIN 
            bon_de_commande b 
        ON 
            p.barcode = b.barcode
        ORDER BY 
            CAST(p.barcode AS UNSIGNED) ASC";
    $stmt = $pdo->query($sql);

    // Requête pour alimenter les filtres
    $familles = $pdo->query("SELECT DISTINCT famille FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $nomsProduits = $pdo->query("SELECT DISTINCT nom_produit FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $codesTypes = $pdo->query("SELECT DISTINCT code_type FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $referencesCommandes = $pdo->query("SELECT DISTINCT b.nom_commande FROM bon_de_commande b")->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afficher les Produits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #555;
        }

        .filter-container {
            margin: 20px auto;
            padding: 10px;
            width: 80%;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .counter {
            text-align: center;
            margin: 10px 0;
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        select, button {
            padding: 5px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function filterProducts() {
            const famille = document.getElementById("famille-select").value.toLowerCase();
            const nomProduit = document.getElementById("nom-produit-select").value.toLowerCase();
            const codeType = document.getElementById("code-type-select").value.toLowerCase();
            const stockage = document.getElementById("stockage-select").value.toLowerCase();
            const reference = document.getElementById("reference-select").value.toLowerCase();

            const rows = document.querySelectorAll("tbody tr");
            let count = 0;

            rows.forEach(row => {
                const [nom, familleRow, code, , , , , stockageRow, ref] = row.children;

                const matchesFamille = !famille || familleRow.textContent.toLowerCase().includes(famille);
                const matchesNomProduit = !nomProduit || nom.textContent.toLowerCase().includes(nomProduit);
                const matchesCodeType = !codeType || code.textContent.toLowerCase().includes(codeType);
                const matchesStockage = !stockage || stockageRow.textContent.toLowerCase().includes(stockage);
                const matchesReference = !reference || ref.textContent.toLowerCase().includes(reference);

                if (matchesFamille && matchesNomProduit && matchesCodeType && matchesStockage && matchesReference) {
                    row.style.display = "";
                    count++;
                } else {
                    row.style.display = "none";
                }
            });

            document.getElementById("product-counter").textContent = `Produits correspondants : ${count}`;
        }
    </script>
</head>
<body>
    <h1>Liste des Produits</h1>

    <div class="filter-container">
        <div class="counter" id="product-counter">Produits correspondants : 0</div>

        <label for="famille-select">Sélectionner une Famille :</label>
        <select id="famille-select" onchange="filterProducts()">
            <option value="">-- Toutes les Familles --</option>
            <?php foreach ($familles as $famille): ?>
                <option value="<?= htmlspecialchars($famille) ?>"><?= htmlspecialchars($famille) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="nom-produit-select">Sélectionner un Nom du Produit :</label>
        <select id="nom-produit-select" onchange="filterProducts()">
            <option value="">-- Tous les Produits --</option>
            <?php foreach ($nomsProduits as $nomProduit): ?>
                <option value="<?= htmlspecialchars($nomProduit) ?>"><?= htmlspecialchars($nomProduit) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="code-type-select">Sélectionner un Code Produit :</label>
        <select id="code-type-select" onchange="filterProducts()">
            <option value="">-- Tous les Codes Produits --</option>
            <?php foreach ($codesTypes as $codeType): ?>
                <option value="<?= htmlspecialchars($codeType) ?>"><?= htmlspecialchars($codeType) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="stockage-select">Sélectionner un État de Stockage :</label>
        <select id="stockage-select" onchange="filterProducts()">
            <option value="">-- Tous les États --</option>
            <option value="En stock">En stock</option>
            <option value="Envoyé">Envoyé</option>
        </select>
        <br>
        <label for="reference-select">Sélectionner une Référence Commande :</label>
        <select id="reference-select" onchange="filterProducts()">
            <option value="">-- Toutes les Références --</option>
            <?php foreach ($referencesCommandes as $referenceCommande): ?>
                <option value="<?= htmlspecialchars($referenceCommande) ?>"><?= htmlspecialchars($referenceCommande) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom du Produit</th>
                <th>Famille</th>
                <th>Code Produit</th>
                <th>Barcode</th>
                <th>Date d'Enregistrement</th>
                <th>Enregistrement bon de commande</th>
                <th>Commercialisation</th>
                <th>Stockage</th>
                <th>Référence Commande</th>
                <th>Destinataire</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rows = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }

            foreach (array_reverse($rows) as $row):
                $isCommercialised = $row['commercialise'] ? "Oui" : "Non";
                $isInStock = $row['date_envoi'] ? "Envoyé" : ($row['en_stock'] ? "En stock" : "Hors stock");
                $dateEnvoi = $row['date_envoi'] ? htmlspecialchars($row['date_envoi']) : "N/A";
                $referenceCommande = $row['reference_commande'] ? htmlspecialchars($row['reference_commande']) : "Aucune";
                $destinataire = $row['destinataire'] ? htmlspecialchars($row['destinataire']) : "Inconnu";
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom_produit']) ?></td>
                    <td><?= htmlspecialchars($row['famille']) ?></td>
                    <td><?= htmlspecialchars($row['code_type']) ?></td>
                    <td><?= htmlspecialchars($row['barcode']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= $dateEnvoi ?></td>
                    <td><?= $isCommercialised ?></td>
                    <td><?= $isInStock ?></td>
                    <td><?= $referenceCommande ?></td>
                    <td><?= $destinataire ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Initialisation du compteur après le chargement de la page
        filterProducts();
    </script>
</body>
</html>