-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql-medical-office-ressources.alwaysdata.net
-- Generation Time: Apr 04, 2024 at 08:37 AM
-- Server version: 10.6.16-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical-office-ressources_bd`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultation`
--

CREATE TABLE `consultation` (
  `idConsultation` int(11) NOT NULL,
  `idMedecin` int(11) NOT NULL,
  `dateConsultation` date NOT NULL,
  `heureDebut` time NOT NULL,
  `duree` tinyint(4) NOT NULL DEFAULT 30,
  `idUsager` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation`
--

INSERT INTO `consultation` (`idConsultation`, `idMedecin`, `dateConsultation`, `heureDebut`, `duree`, `idUsager`) VALUES
(1, 5, '2024-12-16', '08:50:00', 30, 4),
(2, 3, '2024-04-25', '12:00:00', 30, 3),
(3, 3, '2024-06-19', '12:00:00', 30, 2),
(4, 4, '2024-12-23', '21:00:00', 45, 3),
(5, 4, '2030-04-24', '12:00:00', 30, 5),
(6, 10, '2024-04-03', '14:00:00', 40, 6);

-- --------------------------------------------------------

--
-- Table structure for table `medecin`
--

CREATE TABLE `medecin` (
  `idMedecin` int(11) NOT NULL,
  `civilite` varchar(4) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medecin`
--

INSERT INTO `medecin` (`idMedecin`, `civilite`, `nom`, `prenom`) VALUES
(1, 'M.', 'Hirschmuller', 'Aloïs'),
(2, 'Mme.', 'Foucherau', 'Wendy'),
(3, 'M.', 'Maury-Balit', 'Maxence'),
(4, 'M.', 'Koh', 'You-Chen'),
(5, 'Mme.', 'Chokhalov', 'Danna'),
(6, 'Mme.', 'Bonnard', 'Cerise'),
(7, 'M.', 'Louis', 'Enzo'),
(8, 'M.', 'Miled', 'Willem'),
(9, 'Mme.', 'Beaujour', 'Jade'),
(10, 'M.', 'Barthe', 'Stuart-Victor');

-- --------------------------------------------------------

--
-- Table structure for table `usager`
--

CREATE TABLE `usager` (
  `idUsager` int(11) NOT NULL,
  `civilite` varchar(4) NOT NULL,
  `sexe` char(1) NOT NULL,
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
-- Dumping data for table `usager`
--

INSERT INTO `usager` (`idUsager`, `civilite`, `sexe`, `nom`, `prenom`, `adresse`, `ville`, `codePostal`, `numeroSecuriteSociale`, `dateNaissance`, `lieuNaissance`, `medecinReferent`) VALUES
(1, 'Mme.', 'F', 'Chokhalov', 'Rayana', '2 rue de la Saucisse de Strasbourg', 'Strasbourg', '67000', '101010101010101', '2023-02-03', 'Ijevsk', 2),
(2, 'M.', 'H', 'Martos', 'Esteban', '4 avenue de la Cousinade', 'Courbevoie', '92026', '323232323232322', '2015-05-08', 'Paris', 4),
(3, 'Mme.', 'F', 'Ramaroson', 'Francine', '12 place des Minimes', 'Toulouse', '31000', '294294293923293', '1999-11-23', 'Antananarivo', 10),
(4, 'Mme.', 'F', 'Cavé', 'Pauline', 'Villa Saint Paul', 'Puy-L\'Eveque', '46700', '284284283098492', '1983-04-05', 'Cahors', 9),
(5, 'M.', 'H', 'Cavé', 'Jesse', 'Villa Saint Paul', 'Puy-L\'Eveque', '46700', '284294013982943', '2002-09-21', 'Moissac', 9),
(6, 'M.', 'H', 'Château-roux', 'Gojaud', '17 rue Hollow', 'Toulouse', '31400', '881188118811881', '1989-12-07', 'Tokyo', 6),
(7, 'M.', 'H', 'Riley', 'Simon', '5 impasse anonyme', 'Avignon', '84000', '013256458544236', '1942-03-17', 'Avignon', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`idConsultation`),
  ADD KEY `fk_consultation_idusager` (`idUsager`),
  ADD KEY `fk_consultation_idmedecin` (`idMedecin`);

--
-- Indexes for table `medecin`
--
ALTER TABLE `medecin`
  ADD PRIMARY KEY (`idMedecin`);

--
-- Indexes for table `usager`
--
ALTER TABLE `usager`
  ADD PRIMARY KEY (`idUsager`),
  ADD UNIQUE KEY `UN_USAGER_NumSecurite` (`numeroSecuriteSociale`),
  ADD KEY `fk_usager_idmedecin` (`medecinReferent`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `idConsultation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medecin`
--
ALTER TABLE `medecin`
  MODIFY `idMedecin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `usager`
--
ALTER TABLE `usager`
  MODIFY `idUsager` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `fk_consultation_idmedecin` FOREIGN KEY (`idMedecin`) REFERENCES `medecin` (`idMedecin`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_consultation_idusager` FOREIGN KEY (`idUsager`) REFERENCES `usager` (`idUsager`) ON DELETE CASCADE;

--
-- Constraints for table `usager`
--
ALTER TABLE `usager`
  ADD CONSTRAINT `fk_usager_idmedecin` FOREIGN KEY (`medecinReferent`) REFERENCES `medecin` (`idMedecin`) ON DELETE SET NULL ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
