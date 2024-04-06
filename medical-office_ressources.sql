-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql-medical-office.alwaysdata.net
-- Generation Time: Mar 27, 2024 at 11:57 AM
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
-- Database: `medical-office_ressources`
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`idConsultation`),
  ADD UNIQUE KEY `UN_Consultation_MedDateHeure` (`idMedecin`,`dateConsultation`,`heureDebut`),
  ADD KEY `fk_consultation_idusager` (`idUsager`);

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
  ADD CONSTRAINT `fk_usager_idmedecin` FOREIGN KEY (`medecinReferent`) REFERENCES `medecin` (`idMedecin`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `idConsultation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `medecin`
--
ALTER TABLE `medecin`
  MODIFY `idMedecin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `usager`
--
ALTER TABLE `usager`
  MODIFY `idUsager` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `fk_consultation_idmedecin` FOREIGN KEY (`idMedecin`) REFERENCES `medecin` (`idMedecin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultation_idusager` FOREIGN KEY (`idUsager`) REFERENCES `usager` (`idUsager`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
