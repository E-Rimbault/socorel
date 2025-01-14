-- Supprimer la table si elle existe
DROP TABLE IF EXISTS products;

-- Recréer la table avec les modifications demandées
CREATE TABLE IF NOT EXISTS products (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  barcode VARCHAR(20) NOT NULL, -- Modifié pour accepter un format spécifique
  nom_produit varchar(255) NOT NULL,
  code_type int NOT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  famille varchar(100) NOT NULL,
  commercialise tinyint(1) DEFAULT '1',
  en_stock varchar(10) DEFAULT 'stocker',
  date_envoi DATETIME NULL DEFAULT NULL, -- Colonne utilisée pour enregistrer la date d'envoi
  PRIMARY KEY (id),
  UNIQUE KEY unique_barcode (barcode) -- Ajout d'un index unique sur barcode
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Gestion des déclencheurs
DELIMITER $$

-- Supprimer le déclencheur s'il existe déjà
DROP TRIGGER IF EXISTS before_insert_products$$

-- Recréer le déclencheur pour gérer l'insertion
CREATE TRIGGER before_insert_products
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
  DECLARE next_barcode INT;
  DECLARE last_two_digits INT;

  -- Vérifier que le code_type est supérieur ou égal à 1015, sinon l'initialiser
  IF NEW.code_type < 1015 THEN
    SET NEW.code_type = 1015;
  END IF;

  -- Extraire les deux derniers chiffres du code_type
  SET last_two_digits = NEW.code_type % 100;

  -- Compter le nombre de produits existants avec ce code_type
  SELECT COUNT(*) + 1000
  INTO next_barcode
  FROM products
  WHERE code_type = NEW.code_type;

  -- Générer le barcode avec le format : les deux derniers chiffres du code_type + tiret + 4 zéros + numéro
  SET NEW.barcode = CONCAT(LPAD(last_two_digits, 2, '0'), '-', '0000', next_barcode);

  -- Toujours initialiser en_stock à "stocker" lors de l'insertion
  SET NEW.en_stock = 'stocker';
END$$

-- Supprimer le déclencheur de mise à jour s'il existe déjà
DROP TRIGGER IF EXISTS before_update_products$$

-- Créer un déclencheur pour gérer les mises à jour
CREATE TRIGGER before_update_products
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
  -- Vérifier si la colonne en_stock a été modifiée
  IF NEW.en_stock != OLD.en_stock THEN
    -- Mettre à jour la colonne date_envoi avec l'heure actuelle
    SET NEW.date_envoi = NOW();
  END IF;
END$$

DELIMITER ;
