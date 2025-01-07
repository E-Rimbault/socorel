-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 07 jan. 2025 à 10:57
-- Version du serveur : 8.0.31
-- Version de PHP : 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Configuration de la base
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Base de données : `socorel-gestion`

-- --------------------------------------------------------

-- Structure de la table `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `barcode` int UNSIGNED NOT NULL,
  `nom_produit` varchar(255) NOT NULL,
  `code_type` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `famille` varchar(100) NOT NULL,
  `commercialise` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Déchargement des données de la table `products`
INSERT INTO `products` (`id`, `barcode`, `nom_produit`, `code_type`, `created_at`, `famille`, `commercialise`) VALUES
-- (Ajoutez vos données ici)

-- Gestion des déclencheurs
DELIMITER $$

DROP TRIGGER IF EXISTS `before_insert_products`$$
CREATE TRIGGER `before_insert_products`
BEFORE INSERT ON `products`
FOR EACH ROW
BEGIN
  DECLARE next_barcode INT;

  -- Si le code_type est NULL ou inférieur à 1015, initialiser à 1015
  IF NEW.code_type < 1015 THEN
    SET NEW.code_type = 1015;
  END IF;

  -- Récupérer le dernier barcode pour le code_type donné
  SELECT MAX(barcode) + 1 INTO next_barcode
  FROM products
  WHERE code_type = NEW.code_type;

  -- Si aucun barcode trouvé pour ce code_type, initialiser à 1
  IF next_barcode IS NULL THEN
    SET next_barcode = 1;
  END IF;

  -- Affecter le barcode au nouvel enregistrement
  SET NEW.barcode = next_barcode;
END$$

DELIMITER ;

