<?php

    enum RetourAPI {
        case SUCCES;
        case AUCUNE_LIGNE;
        case ERREUR;
    }

    function verifierAuthentification(){
        if (!isset($_SESSION['utilisateur']) || empty($_SESSION['utilisateur'])){
            header('Location: authentification.php'); exit();
        }
    }

    function verifierPrepare($stmt){
        if (!$stmt) { 
            echo "Erreur lors d'un prepare statement : " . $stmt->errorInfo(); exit(); 
        }
    }

    function verifierExecute($stmt){
        if (!$stmt) {
            echo "Erreur lors d'un execute statement : " . $stmt->errorInfo(); exit(); 
        }
    }

    function formaterDate($date){
        $elementsDate = explode('-', $date);
        $dateFormatee = $elementsDate[2] . '/' . $elementsDate[1] . '/' . $elementsDate[0];
        return $dateFormatee;
    }

 
function horaireChevauchantePourMedecin(array $consultations, string $date, string $heure, string $duree, int $idMedecin) : bool {
    foreach ($consultations as $consultation) {
        if ($idMedecin == $consultation["idMedecin"] &&
            $date == $consultation["dateConsultation"] &&
            consultationsChevauchantes($heure, $duree, substr($consultation['heureDebut'], 0, 5), substr($consultation['duree'], 0, 5))) {
                return true;
        }
    }
    return false;
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
?>