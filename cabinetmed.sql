-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 19 jan. 2024 à 18:42
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cabinetmed`
--

-- --------------------------------------------------------

--
-- Structure de la table `consultation`
--

CREATE TABLE `consultation` (
  `idMedecin` int(11) NOT NULL,
  `dateConsultation` date NOT NULL,
  `heureDebut` time NOT NULL,
  `duree` time NOT NULL DEFAULT '00:30:00',
  `idUsager` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `consultation`
--

INSERT INTO `consultation` (`idMedecin`, `dateConsultation`, `heureDebut`, `duree`, `idUsager`) VALUES
(2, '2024-01-25', '10:00:00', '00:55:00', 44),
(2, '2024-01-31', '15:30:00', '00:32:00', 1),
(14, '2024-01-31', '15:30:00', '00:25:00', 45),
(14, '2024-01-31', '16:00:00', '00:35:00', 45);

-- --------------------------------------------------------

--
-- Structure de la table `medecin`
--

CREATE TABLE `medecin` (
  `idMedecin` int(11) NOT NULL,
  `civilite` varchar(4) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `medecin`
--

INSERT INTO `medecin` (`idMedecin`, `civilite`, `nom`, `prenom`) VALUES
(1, 'Mme', 'Vanessara', 'Aftonsa'),
(2, 'M', 'Duchamp', 'Raphaël'),
(3, 'M', 'Moulin', 'Paul'),
(13, 'Mme', 'Vanessa', 'Aftonne'),
(14, 'Mme', 'Cara', 'Enzod'),
(18, 'M', 'Moulin', 'Wendy');

-- --------------------------------------------------------

--
-- Structure de la table `usager`
--

CREATE TABLE `usager` (
  `idUsager` int(11) NOT NULL,
  `civilite` varchar(4) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `ville` varchar(50) NOT NULL,
  `codePostal` char(5) NOT NULL,
  `numeroSecuriteSociale` char(15) NOT NULL,
  `dateNaissance` date NOT NULL,
  `lieuNaissance` varchar(50) NOT NULL,
  `medecinReferent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `usager`
--

INSERT INTO `usager` (`idUsager`, `civilite`, `nom`, `prenom`, `adresse`, `ville`, `codePostal`, `numeroSecuriteSociale`, `dateNaissance`, `lieuNaissance`, `medecinReferent`) VALUES
(1, 'M', 'Moulin', 'Pierre', '2 Rue Alberto Dubois', 'Toulouse', '31000', '012345678912345', '1986-08-03', 'Toulouse', 2),
(2, 'M', 'Nico', 'Samuel', '3 Impasse du roi', 'Montauban', '82000', '555554444466666', '2001-11-15', 'Toulouse', NULL),
(28, 'M', 'Moulin', 'Paul', '114 Route', 'Tours', '55000', '012345678912349', '2023-12-31', 'Toulouse', 13),
(29, 'M', 'Moulin', 'sef', '114 Route', 'Toulouse', '15000', '012345678912350', '2023-12-31', 'Toulouse', NULL),
(42, 'M', 'Moulin', 'Wendy', '27 chemin du Vert', 'Lyon', '15000', '012345678912341', '2023-09-14', 'Toulouse', 3),
(44, 'Mme', 'Moulin', 'Pierre', '114 Route', 'Toulouse', '15000', '012345678912332', '2023-12-23', 'Toulouse', 2),
(45, 'M', 'Deschamps', 'Esteban', '4 rue Pablo', 'Montauban', '82000', '954498489126985', '2023-12-26', 'Toulouse', 14);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`idMedecin`,`dateConsultation`,`heureDebut`),
  ADD KEY `fk_consultation_idusager` (`idUsager`);

--
-- Index pour la table `medecin`
--
ALTER TABLE `medecin`
  ADD PRIMARY KEY (`idMedecin`),
  ADD UNIQUE KEY `UN_MEDECIN_NomPrenom` (`nom`,`prenom`);

--
-- Index pour la table `usager`
--
ALTER TABLE `usager`
  ADD PRIMARY KEY (`idUsager`),
  ADD UNIQUE KEY `UN_USAGER_NumSecurite` (`numeroSecuriteSociale`),
  ADD KEY `fk_usager_idmedecin` (`medecinReferent`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `medecin`
--
ALTER TABLE `medecin`
  MODIFY `idMedecin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `usager`
--
ALTER TABLE `usager`
  MODIFY `idUsager` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `fk_consultation_idmedecin` FOREIGN KEY (`idMedecin`) REFERENCES `medecin` (`idMedecin`),
  ADD CONSTRAINT `fk_consultation_idusager` FOREIGN KEY (`idUsager`) REFERENCES `usager` (`idUsager`);

--
-- Contraintes pour la table `usager`
--
ALTER TABLE `usager`
  ADD CONSTRAINT `fk_usager_idmedecin` FOREIGN KEY (`medecinReferent`) REFERENCES `medecin` (`idMedecin`),
  ADD CONSTRAINT `usager_ibfk_1` FOREIGN KEY (`medecinReferent`) REFERENCES `medecin` (`idMedecin`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
