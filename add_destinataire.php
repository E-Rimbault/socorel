<?php
// add_destinataire.php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_destinataire = trim($_POST['nom_destinataire']);

    if (!empty($nom_destinataire)) {
        try {
            // Vérifier si le destinataire existe déjà
            $sql = "SELECT COUNT(*) FROM destinataire WHERE nom_destinataire = :nom_destinataire";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nom_destinataire', $nom_destinataire, PDO::PARAM_STR);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists > 0) {
                echo "<script>
                    alert('Erreur : Ce destinataire existe déjà.');
                    window.history.back();
                </script>";
            } else {
                // Ajouter le destinataire dans la base de données
                $sql = "INSERT INTO destinataire (nom_destinataire) VALUES (:nom_destinataire)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nom_destinataire', $nom_destinataire, PDO::PARAM_STR);
                $stmt->execute();
                echo "<script>
                    alert('Destinataire ajouté avec succès.');
                    window.close();
                </script>";
            }
        } catch (PDOException $e) {
            echo "<script>
                alert('Erreur lors de l’ajout : " . htmlspecialchars($e->getMessage()) . "');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('Veuillez remplir le nom du destinataire.');
            window.history.back();
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Destinataire</title>
</head>
<body>
    <h1>Ajouter un Destinataire</h1>
    <form method="POST" action="">
        <label for="nom_destinataire">Nom du Destinataire :</label>
        <input type="text" id="nom_destinataire" name="nom_destinataire" required>
        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
