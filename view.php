<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afficher les Produits</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .scroll-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .filter-container {
            margin-bottom: 15px;
        }
        .manage-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Liste des Produits</h1>

    <div class="filter-container">
        <label for="famille-select">Sélectionner une Famille :</label>
        <select id="famille-select">
            <option value="">-- Toutes les Familles --</option>
        </select>

        <label for="nom-produit-select">Sélectionner un Nom du Produit :</label>
        <select id="nom-produit-select">
            <option value="">-- Tous les Produits --</option>
        </select>
    </div>

    <div class="scroll-container">
        <table>
            <thead>
                <tr>
                    <th>Nom du Produit</th>
                    <th>Famille</th>
                    <th>Code Type</th>
                    <th>Barcode</th>
                    <th>Date d'Enregistrement</th>
                    <th>Commercialisation</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require 'database.php';

                try {
                    $sql = "SELECT * FROM products ORDER BY famille ASC, nom_produit ASC";
                    $stmt = $pdo->query($sql);

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $isCommercialised = $row['commercialise'] ? "Oui" : "Non";
                        echo "<tr data-famille='" . htmlspecialchars($row['famille']) . "' data-nom-produit='" . htmlspecialchars($row['nom_produit']) . "'>";
                        echo "<td>" . htmlspecialchars($row['nom_produit']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['famille']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['code_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['barcode']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                        echo "<td>" . $isCommercialised . "</td>";
                        echo "</tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6'>Erreur : " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="manage-container">
        <h2>Gérer la Commercialisation</h2>
        <form action="manage_commercialisation.php" method="POST">
            <label for="code_type">Code Type de la Gamme :</label>
            <input type="number" name="code_type" id="code_type" required>
            <br><br>
            <label for="status">Statut :</label>
            <select name="status" id="status" required>
                <option value="1">Commercialisé</option>
                <option value="0">Non Commercialisé</option>
            </select>
            <br><br>
            <button type="submit">Mettre à Jour</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('tbody tr');
            const nomProduitSelect = document.getElementById('nom-produit-select');
            const familleSelect = document.getElementById('famille-select');

            const nomProduitSet = new Set();
            const familleSet = new Set();

            rows.forEach(row => {
                const nomProduit = row.getAttribute('data-nom-produit');
                const famille = row.getAttribute('data-famille');

                if (nomProduit) nomProduitSet.add(nomProduit);
                if (famille) familleSet.add(famille);
            });

            // Populate Famille select
            Array.from(familleSet).sort().forEach(famille => {
                const option = document.createElement('option');
                option.value = famille;
                option.textContent = famille;
                familleSelect.appendChild(option);
            });

            // Populate Nom Produit select
            Array.from(nomProduitSet).sort().forEach(nomProduit => {
                const option = document.createElement('option');
                option.value = nomProduit;
                option.textContent = nomProduit;
                nomProduitSelect.appendChild(option);
            });

            function filterRows() {
                const selectedNomProduit = nomProduitSelect.value;
                const selectedFamille = familleSelect.value;

                rows.forEach(row => {
                    const rowNomProduit = row.getAttribute('data-nom-produit');
                    const rowFamille = row.getAttribute('data-famille');

                    if (
                        (selectedNomProduit === '' || rowNomProduit === selectedNomProduit) &&
                        (selectedFamille === '' || rowFamille === selectedFamille)
                    ) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            nomProduitSelect.addEventListener('change', filterRows);
            familleSelect.addEventListener('change', filterRows);
        });
    </script>
</body>
</html>
