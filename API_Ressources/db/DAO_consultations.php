<?php

require("../utils/utilitaires.php");

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
    $stmt = $pdo->prepare("SELECT *, DATE_FORMAT(consultation.dateConsultation, '%d/%m/%Y') as dateConsultation, TIME_FORMAT(consultation.heureDebut, '%H:%i') as heureDebut 
                            FROM consultation WHERE idConsultation = ?");

    if ($stmt && $stmt->execute([$idConsultation])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return array();
}

/**
 * Récupérer toutes les consultations possiblement filtrées
 * 
 * @param PDO           $pdo        le pdo reliant à la bdd
 * @param int|null      $idMedecin  l'id du médecin potentiel
 * @param int|null      $idUsager   l'id du patient potentiel 
 * @param string|null   $date       la potentielle date
 * 
 * @return array renvoie la liste de toutes les consultations, possiblement filtrées si
 *               des filtres ont été renseignés
 */
function getConsultations(PDO $pdo, int | null $idMedecin, int | null $idUsager, string | null $date) : array {
    $reqConsultations = "SELECT idConsultation,
                                c.idMedecin, 
                                c.idUsager,
                                CONCAT(m.nom, ' ', m.prenom) as nomMedecin, 
                                CONCAT(u.nom, ' ', u.prenom) as nomUsager,
                                DATE_FORMAT(c.dateConsultation, '%d/%m/%Y') as dateConsultation, 
                                TIME_FORMAT(c.heureDebut, '%H:%i') as heureDebut, 
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
        if (!$dateFormatee = DateTime::createFromFormat('d/m/y', $date)) {
            return array();
        }
        $reqConsultations = $reqConsultations . "AND dateConsultation = ? ";
        array_push($arguments, $dateFormatee->format('y-m-d'));
    }

    $reqConsultations = $reqConsultations . "ORDER BY idConsultation";

        
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
function addConsultation(PDO $pdo, int $idMedecin, int $idUsager, string $date, string $heure, string $duree) : int {
    if (consultationChevauchantePourMedecin($pdo, $idMedecin, $date, $heure, $duree)) {
        return -1;
    }

    $pdo->beginTransaction(); 
    try {
        $stmt = $pdo->prepare("INSERT INTO consultation(idMedecin, dateConsultation, heureDebut, duree, idUsager) VALUES (?, ?, ?, ?, ?)");
        
        $dateFormatee = DateTime::createFromFormat('d/m/y', $date);
        $idConsultation = $stmt->execute([$idMedecin, $dateFormatee->format('y-m-d'), $heure, $duree, $idUsager]) ? $pdo->lastInsertId() : null;
        $pdo->commit();
        return $idConsultation;
    } catch (PDOException $exception) {
        // Violation de la contrainte de clé étrangère pour l'id du médecin ou de l'usager
        if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1452') !== false) {
            return -2;
        } else {
            return 0;
        }
    }
}

/**
 * Vérifie si la date, l'heure et la durée passés en paramètres vont chevaucher avec une 
 * autre consultation du médecin passé en paramètre
 *
 * @param PDO $pdo              PDO établissant la connexion à la base de données
 * @param integer $idMedecin    ID du médecin concerné
 * @param string $date          Date de la consultation à vérifier
 * @param string $heure         Heure de la consultation à vérifier
 * @param string $duree         Duree de la consultation à vérifier
 * @return boolean              VRAI si la consultation chevauche, FAUX sinon
 */
function consultationChevauchantePourMedecin(PDO $pdo, int $idMedecin, string $date, string $heure, string $duree, int | null $idConsultation = null) : bool {
    $consultations = getConsultations($pdo, $idMedecin, null, null);
    $dateFormatee = DateTime::createFromFormat('d/m/y', $date);
    $dateFormatee = $dateFormatee->format('Y-m-d');

    foreach ($consultations as $consultation) {
        $dateFormateeTemp = DateTime::createFromFormat('d/m/Y', $consultation["dateConsultation"]);
        $dateFormateeTemp = $dateFormateeTemp->format('Y-m-d');

        if ($dateFormatee == $dateFormateeTemp &&
            $consultation["idConsultation"] != $idConsultation &&
            horairesChevauchantes($heure, $duree, $consultation['heureDebut'], $consultation['duree'])) {
                return true;
        }
    }
    return false;
}

/**
 * Vérifie si deux horaire passés en paramètres chevauchent
 *
 * @param string $heureDebutC1  Heure de début du premier horaire
 * @param string $dureeC1       Duree du premier horaire
 * @param string $heureDebutC2  Heure de début du second horaire
 * @param string $dureeC2       Duree du second horaire
 * @return bool                 VRAI si les horaires chevauchent, FAUX sinon
 */
function horairesChevauchantes(string $heureDebutC1, string $dureeC1, string $heureDebutC2, string $dureeC2) : bool {
    // On crée les dates de début et de fin des deux consultations
    $debutC1 = convertirEnMinutes($heureDebutC1);
    $finC1 = $debutC1 + $dureeC1;

    $debutC2 = convertirEnMinutes($heureDebutC2);
    $finC2 = $debutC2 + $dureeC2;
    
    // On vérifie si les consultations se chevauchent
    if (($debutC1 >= $debutC2 && $debutC1 < $finC2) ||
        ($finC1 > $debutC2 && $finC1 <= $finC2) || 
        ($debutC2 >= $debutC1 && $debutC2 < $finC1)) {
            return true;
    }
    return false;
}

/**
 * Converti une chaine au format 'HH:MM' en minutes
 *
 * @param string $heure      Chaine au format 'HH:MM' 
 * @return int               Minutes totales
 */
function convertirEnMinutes(string $heure): int {
    list($heures, $minutes) = explode(':', $heure);
    return ($heures * 60) + $minutes;
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
 * Modifie une consultation
 * 
 * @param PDO $pdo              le pdo reliant à la bdd
 * @param int $idConsultation   l'identifiant de la consultation à modifier 
 * @param int|null $heure       l'heure à potentiellement modifer
 * @param int|null $duree       la durée à potentiellement modifier 
 */

function editConsultation(PDO $pdo, int $idConsultation, int | null $idUsager, int | null $idMedecin, string | null $date, string | null $heure, int | null $duree) : int {
    $idConsultationAvantModif = getConsultationById($pdo, $idConsultation);
    
    $idMedecinTemp = $idMedecin ?? $idConsultationAvantModif["idMedecin"];
    $dateTemp = $date ?? $idConsultationAvantModif["dateConsultation"];
    $heureTemp = $heure ?? $idConsultationAvantModif["heureDebut"];
    $dureeTemp = $duree ?? $idConsultationAvantModif["duree"];
    if (consultationChevauchantePourMedecin($pdo, $idMedecinTemp, $dateTemp, $heureTemp, $dureeTemp, $idConsultation)) {
        return -1;
    }

    $sql = "UPDATE consultation SET";

    $dateFormatee = DateTime::createFromFormat('d/m/y', $date);
    $criteres = ["idUsager" => $idUsager, "idMedecin" => $idMedecin, "dateConsultation" => $dateFormatee->format('y-m-d'), "heureDebut" => $heure, "duree" => $duree];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? ", " : " ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }

    // Si aucune colonne n'est modifiée, on retourne 0
    if (!$unCritere) { return 0; }

    $arguments["idConsultation"] = $idConsultation;
    $sql .= " WHERE idConsultation = :idConsultation";

    try {
        // Préparation & exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($arguments);
        return $stmt->rowCount();
    } catch (PDOException $exception ) {
        // Violation de la contrainte de clé étrangère pour l'id du médecin ou de l'usager
        if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1452') !== false) {
            return -2;
        } else {
            return 0;
        }
    }
}

