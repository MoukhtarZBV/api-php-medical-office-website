<?php

require("../functions/fonctions.php");

function getUsagerById(PDO $pdo, int $idUsager) : array {
    $stmt = $pdo->prepare("SELECT * FROM usager WHERE idUsager = ?");
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idUsager])) {
        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $res;
        }
    }
    return array();
}

function getUsagers(PDO $pdo, int | null $idusager, string | null $civilite, string | null $nom, 
string | null $prenom, string | null $numSS) : array | bool {

    $sql = "SELECT * FROM usager";
    // Recherche en fonction des filtres
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom, "numeroSecuriteSociale" => $numSS];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? " AND " : " WHERE ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
        
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute($arguments)) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }

}

function addUsager(PDO $pdo, string $civilite, string $nom, string $prenom, string $adresse, string $ville,
    string $codePostal, string $numeroSecuriteSociale, string $dateNaissance, string $lieuNaissance, int | null $medecinReferent) : int | null {
    $stmt = $pdo->prepare("INSERT INTO usager(civilite, nom, prenom, adresse, ville, codePostal, numeroSecuriteSociale, dateNaissance, lieuNaissance, medecinReferent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return null;
    }

    $pdo->beginTransaction();
    $idUsager = $stmt->execute([$civilite, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale, $dateNaissance, $lieuNaissance, $medecinReferent]) ? $pdo->lastInsertId() : null;
    $pdo->commit();
    return $idUsager;
}

function deleteUsager(PDO $pdo, int $idUsager) : bool {
    $stmt = $pdo->prepare("DELETE FROM usager WHERE idUsager = ?");
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute([$idUsager])) {
        return $stmt->rowCount() > 0;
    }
    return false;
}

function editUsager(PDO $pdo, int $idUsager, string | null $civilite, string | null $nom, string | null $prenom, 
    string | null $adresse, string | null $ville, string | null $codePostal, string | null $numeroSecuriteSociale, 
    string | null $dateNaissance, string | null $lieuNaissance, int | null $medecinReferent)  : bool {
    
    $sql = "UPDATE usager SET";
    $criteres = ["idUsager" => $idUsager, "civilite" => $civilite, "nom" => $nom, "prenom" => $prenom, "adresse" => $adresse, "ville" => $ville,
    "codePostal" => $codePostal, "numeroSecuriteSociale" => $numeroSecuriteSociale, "dateNaissance" => $dateNaissance,
    "lieuNaissance" => $dateNaissance, "medecinReferent" => $medecinReferent];
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
    $arguments["idUsager"] = $idUsager;
    $sql .= " WHERE idUsager = :idUsager";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }

    return $stmt->execute($arguments);
  
}