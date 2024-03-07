<?php

require("../functions/fonctions.php");

function getConsultationById(PDO $pdo, int $idConsultation) : array {
    $stmt = $pdo->prepare("SELECT * FROM consultation WHERE idConsultation = ?");
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idConsultation])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return array();
}

function getConsultations(PDO $pdo, int | null $idMedecin, int | null $idUsager, string | null $date) : array {
    $reqConsultations = "SELECT idConsultation,
                                CONCAT(m.nom, ' ', m.prenom) as nomMedecin, 
                                CONCAT(u.nom, ' ', u.prenom) as nomUsager,
                                dateConsultation, 
                                heureDebut, 
                                duree
                            FROM medecin m, usager u, consultation c 
                            WHERE c.idMedecin = m.idMedecin 
                            AND c.idUsager = u.idUsager ";

    $arguments = array();

    // Recherche en fonction des filtres
    if (!empty($idMedecin)) {
        $reqConsultations = $reqConsultations . "AND c.idMedecin = ? ";
        array_push($arguments, $idMedecin);
    }
    if (!empty($idUsager)) {
        $reqConsultations = $reqConsultations . "AND c.idUsager = ? ";
        array_push($arguments, $idUsager);
    }
    if (!empty($date)) {
        $reqConsultations = $reqConsultations . "AND dateConsultation = ? ";
        array_push($arguments, $date);
    }

    $reqConsultations = $reqConsultations . "ORDER BY dateConsultation DESC, heureDebut DESC;";

        
    $stmt = $pdo->prepare($reqConsultations);
    verifierPrepare($stmt);
    verifierExecute($stmt->execute($arguments));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addConsultation(PDO $pdo, int $idMedecin, int $idUsager, string $date, string $heure, string $duree) : int | null {
    $stmt = $pdo->prepare("INSERT INTO consultation(idMedecin, dateConsultation, heureDebut, duree, idUsager) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return null;
    }

    $pdo->beginTransaction();
    $idConsultation = $stmt->execute([$idMedecin, $date, $heure, $duree, $idUsager]) ? $pdo->lastInsertId() : null;
    $pdo->commit();
    return $idConsultation;
}

function deleteConsultation(PDO $pdo, int $idConsultation) : bool {
    $stmt = $pdo->prepare("DELETE FROM consultation WHERE idConsultation = ?");
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute([$idConsultation])) {
        return $stmt->rowCount() > 0;
    }
    return false;
}

function editConsultation(PDO $pdo, int $idConsultation, int | null $heure, int | null $duree) : bool {
    $sql = "UPDATE consultation SET ";

    $arguments = array();
    $unCritere = false;
    if (!empty($heure)) {
        $sql .= "heureDebut = :heure";
        $arguments["heure"] = $heure;
        $unCritere = true;
    } 
    if (!empty($duree)) {
        $sql .= ($unCritere ? ", " : "") . "duree = :duree";
        $arguments["duree"] = $duree;
        $unCritere = true;
    }
    if (!$unCritere) {
        return false;
    }
    $arguments["idConsultation"] = $idConsultation;

    $sql .= " WHERE idConsultation = :idConsultation";
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    return $stmt->execute($arguments);
}