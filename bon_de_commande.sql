-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 13 jan. 2025 à 14:55
-- Version du serveur : 8.0.31
-- Version de PHP : 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `socorel-gestion`
--

-- --------------------------------------------------------

--
-- Structure de la table `bon_de_commande`
--

DROP TABLE IF EXISTS `bon_de_commande`;
CREATE TABLE IF NOT EXISTS `bon_de_commande` (
  `commande_id` int NOT NULL AUTO_INCREMENT,
  `nom_commande` varchar(100) NOT NULL,
  `nom_produit` varchar(255) NOT NULL,
  `famille` varchar(255) DEFAULT NULL,
  `code_type` int DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `destinataire` varchar(255) NOT NULL,
  `etat_commande` enum('complet','partiel') NOT NULL,
  PRIMARY KEY (`commande_id`),
  KEY `fk_bon_de_commande_barcode` (`barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bon_de_commande`
--
ALTER TABLE `bon_de_commande`
  ADD CONSTRAINT `fk_bon_de_commande_barcode` FOREIGN KEY (`barcode`) REFERENCES `products` (`barcode`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
