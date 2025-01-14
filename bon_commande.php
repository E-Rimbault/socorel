<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer Bon de Commande</title>
    <style>
        /* Styles CSS */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        h1, h2 {
            text-align: center;
            color: #555;
        }
        .container {
            margin: 20px auto;
            padding: 10px;
            width: 85%;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        select, input[type="number"], input[type="text"], button {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            width: 98%;
        }
        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1>Enregistrer un Bon de Commande</h1>
    <div class="container">
        <form id="commande-form" action="process_commande.php" method="POST" onsubmit="return prepareCommande()">
            <label for="nom_commande">Nom du Bon de Commande :</label>
            <input type="text" id="nom_commande" name="nom_commande" required aria-required="true">

            <label for="destinataire_commande">Destinataire :</label>
            <select id="destinataire_commande" name="destinataire_commande" required aria-required="true" onchange="updateDestinataireHidden()">
                <option value="">Sélectionner un destinataire</option>
                <?php
                include 'database.php';
                try {
                    $sql = "SELECT id, nom_destinataire FROM destinataire";
                    $stmt = $pdo->query($sql);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row['nom_destinataire']) . "'>" . htmlspecialchars($row['nom_destinataire']) . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=''>Erreur : Veuillez réessayer</option>";
                }
                ?>
                <option value="new">Nouveau destinataire</option>
            </select>
            <input type="hidden" id="nom_destinataire_hidden" name="nom_destinataire_hidden">

            <script>
function updateDestinataireHidden() {
    const destinataireSelect = document.getElementById('destinataire_commande');
    const hiddenInput = document.getElementById('nom_destinataire_hidden');

    if (destinataireSelect.value === 'new') {
        const popup = window.open('add_destinataire.php', 'AjoutDestinataire', 'width=500,height=400');
        
        // Surveiller la fermeture de la fenêtre
        const interval = setInterval(() => {
            if (popup.closed) {
                clearInterval(interval);
                // Recharger la page principale
                location.reload();
            }
        }, 500);
    } else {
        hiddenInput.value = destinataireSelect.value;
    }
}
            </script>

            <h2>Liste des Produits</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sélectionner</th>
                        <th>Nom du Produit</th>
                        <th>Code Type</th>
                        <th>Stock Restant</th>
                        <th>Quantité Souhaitée</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $sql = "
                            SELECT 
                                p1.code_type, 
                                p1.nom_produit, 
                                COUNT(*) AS stock_rest 
                            FROM products p1
                            WHERE p1.en_stock = 'stocker'
                            GROUP BY p1.code_type, p1.nom_produit
                        ";
                        $stmt = $pdo->query($sql);

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td><input type='checkbox' class='product-checkbox' onchange='updateRecap()'></td>";
                                echo "<td class='name'>" . htmlspecialchars($row['nom_produit']) . "</td>";
                                echo "<td class='code-type'>" . htmlspecialchars($row['code_type']) . "</td>";
                                echo "<td class='stock'>" . htmlspecialchars($row['stock_rest']) . "</td>";
                                echo "<td><input type='number' class='quantity' value='1' min='1' max='" . htmlspecialchars($row['stock_rest']) . "' onchange='updateRecap()'></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Aucun produit trouvé</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5'>Erreur lors de la récupération des produits</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <input type="hidden" id="produits-selectionnes" name="produits" value="">

            <h2>Récapitulatif de la Commande</h2>
            <div id="recapitulatif" role="region" aria-live="polite">
                <!-- Produits sélectionnés -->
            </div>

            <button type="submit">Confirmer la Commande</button>
        </form>
    </div>

    <script>
        function updateRecap() {
            const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
            const recap = document.getElementById('recapitulatif');
            const hiddenInput = document.getElementById('produits-selectionnes');

            const produits = [];
            recap.innerHTML = '<ul>';

            selectedProducts.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const codeType = row.querySelector('.code-type').textContent;
                const name = row.querySelector('.name').textContent;
                const quantityInput = row.querySelector('.quantity');
                const quantity = quantityInput ? quantityInput.value : 1;

                recap.innerHTML += `<li>${quantity} x ${name} (Code Type: ${codeType})</li>`;

                produits.push({ code: codeType, quantite: quantity });
            });

            recap.innerHTML += '</ul>';
            hiddenInput.value = JSON.stringify(produits);
        }

        function prepareCommande() {
            const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
            if (selectedProducts.length === 0) {
                alert('Veuillez sélectionner au moins un produit.');
                return false;
            }
            return confirm('Confirmez-vous la commande ?');
        }
    </script>
</body>
</html>
