<?php

require("../functions/fonctions.php");

function getMedecinById(PDO $pdo, int $idMedecin) : array {
    $stmt = $pdo->prepare("SELECT * FROM medecin WHERE idMedecin = ?");
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idMedecin])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return array();
}

function getMedecins(PDO $pdo, int | null $idMedecin, string | null $civilite, string | null $nom, string | null $prenom) : array | bool {
    $sql = "SELECT idMedecin, civilite, nom, prenom FROM medecin";
    // Recherche en fonction des filtres
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? " AND " : " WHERE ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
    $sql .= " ORDER BY dateConsultation DESC, heureDebut DESC;";
        
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute([$arguments])) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}

function addMedecin(PDO $pdo, string $civilite, string $nom, string $prenom) : int | null {
    $stmt = $pdo->prepare("INSERT INTO medecin(civilite, nom, prenom) VALUES (?, ?, ?)");
    if (!$stmt) {
        return null;
    }

    $pdo->beginTransaction();
    $idMedecin = $stmt->execute([$civilite, $nom, $prenom]) ? $pdo->lastInsertId() : null;
    $pdo->commit();
    return $idMedecin;
}

function deleteMedecin(PDO $pdo, int $idMedecin) : bool {
    $stmt = $pdo->prepare("DELETE FROM medecin WHERE idMedecin = ?");
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute([$idMedecin])) {
        return $stmt->rowCount() > 0;
    }
    return false;
}

function editMedecin(PDO $pdo, int $idMedecin, string | null $civilite, string | null $nom, string | null $prenom) : bool {
    $sql = "UPDATE medecin SET";
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? ", " : " ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }

    if (!$unCritere) { return false; }
    $arguments["idMedecin"] = $idMedecin;
    $sql .= " WHERE idMedecin = :idMedecin";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    return $stmt->execute($arguments);
}