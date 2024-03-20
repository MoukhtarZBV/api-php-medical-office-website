<?php

require("../../utils/fonctions.php");

/**
 * Récupérer une consultation par son identifiant 
 * 
 * @param PDO $pdo              le pdo reliant à la bdd
 * @param int $idConsultation   l'id à rechercher 
 * 
 * @return array|bool renvoie l'array correspondant à la consultation
 *                    OU le booléen false/un array vide s'il y a eu une erreur 
 */
function getConsultationById(PDO $pdo, int $idConsultation) : array | bool {
    $stmt = $pdo->prepare("SELECT * FROM consultation WHERE idConsultation = ?");
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idConsultation])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return array();
}

/**
 * Récupérer toutes les consultations possiblement filtrées
 * 
 * @param PDO           $pdo        le pdo reliant à la bdd
 * @param int|null      $idMédecin  l'id du médecin potentiel
 * @param int|null      $idUsager   l'id du patient potentiel 
 * @param string|null   $date       la potentielle date
 * 
 * @return array renvoie la liste de toutes les consultations, possiblement filtrées si
 *               des filtres ont été renseignés
 */
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

/**
 * Ajouter une consultation à la bdd 
 * 
 * @param PDO $pdo          Le pdo reliant à la bdd
 * @param int $idMedecin    l'identifiant du médecin s'occupant de la consultation
 * @param int $idUsager     l'identifiant de l'usager concerné par la consultation
 * @param string $date      la date de la consultation
 * @param string $heure     l'heure de la consultation
 * @param string $duree     la durée de la consultation
 * 
 * @return int|null renvoie l'identifiant de la consultation ajoutée OU null si rien n'a été ajouté 
 */
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

/**
 * Supprime une consultation de la bdd 
 * 
 * @param PDO $pdo              le pdo reliant à la bdd
 * @param int $idConsultation   l'identifiant de la consultation à supprimer
 * 
 * @return bool renvoie un booléen indiquant si la suppression a eu lieu 
 */
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

/**
 * Modifie une consultation (REMARQUE: on ne peut modifier que l'heure et la durée d'une consultation)
 * 
 * @param PDO $pdo              le pdo reliant à la bdd
 * @param int $idConsultation   l'identifiant de la consultation à modifier 
 * @param int|null $heure       l'heure à potentiellement modifer
 * @param int|null $duree       la durée à potentiellement modifier 
 */
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

/**
 * 
 */
function horaireChevauchantePourMedecin(PDO $pdo, string $date, string $heure, string $duree, int $idMedecin) : bool {
    $stmt = $pdo->prepare("SELECT heureDebut, duree FROM Consultation c, Medecin m WHERE c.idMedecin = m.idMedecin AND m.idMedecin = ? AND dateConsultation = ?");
    verifierPrepare($stmt);
    verifierExecute($stmt->execute([$idMedecin, $date]));

    $consulationsChevauchantes = false;
    while (!$consulationsChevauchantes && $consultation = $stmt->fetch()){
        if (consultationsChevauchantes($heure, $duree, substr($consultation['heureDebut'], 0, 5), substr($consultation['duree'], 0, 5))) {
            $consulationsChevauchantes = true;
            break;
        }
    }
    return $consulationsChevauchantes;
}

function consultationsChevauchantes($heureDebutC1, $dureeC1, $heureDebutC2, $dureeC2) {
    // On crée les dates de début et de fin des deux consultations
    $debutC1 = DateTime::createFromFormat('H:i', $heureDebutC1);
    $finC1 = clone $debutC1;
    list($hours, $minutes) = explode(':', $dureeC1);
    $finC1->add(new DateInterval("PT{$hours}H{$minutes}M"));

    $debutC2 = DateTime::createFromFormat('H:i', $heureDebutC2);
    $finC2 = clone $debutC2;
    list($hours, $minutes) = explode(':', $dureeC2);
    $finC2->add(new DateInterval("PT{$hours}H{$minutes}M"));

    // On vérifie si les consultations se chevauchent
    if (($debutC1 >= $debutC2 AND $debutC1 < $finC2) ||
        ($finC1 > $debutC2 AND $finC1 <= $finC2) || 
        ($debutC2 >= $debutC1 AND $debutC2 < $finC1)) {
            return true;
    }
    return false;
}