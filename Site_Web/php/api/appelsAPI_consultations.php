<?php

$urlAPI_consultations = "https://medical-office-ressources.alwaysdata.net/consultations";

function API_getConsultations(string | null $idMedecin, string | null $idUsager, string | null $date) : array | int {
    $url = $GLOBALS["urlAPI_consultations"];

    $ch = curl_init();
    $premierFiltre = true;
    if (!empty($idMedecin)) {
        $url .= ($premierFiltre ? '?' : '&') . 'idMedecin=' . $idMedecin;
        $premierFiltre = false;
    }
    if (!empty($idUsager)) {
        $url .= ($premierFiltre ? '?' : '&') . 'idUsager=' . $idUsager;
        $premierFiltre = false;
    }
    if (!empty($date)) {
        $url .= ($premierFiltre ? '?' : '&') . 'dateConsultation=' . $date;
    } 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $authorization = "Authorization: Bearer ".$_SESSION["jwt"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
   
    $resultat = curl_exec($ch);
    curl_close($ch);

    $resultat = json_decode($resultat, true);
    if (200 <= $resultat["statutCode"] && $resultat["statutCode"] < 300) {
        return $resultat["donnees"];
    } else {
        return -1;
    }
}

function API_addConsultation(int $idMedecin, int $idUsager, string $date, string $heure, string $duree) : bool | array {
    $url = $GLOBALS["urlAPI_consultations"];

    $infosConsultation = array(
        "idMedecin" => $idMedecin, 
        "idUsager" => $idUsager, 
        "date" => $date, 
        "heure" => $heure, 
        "duree" => $duree
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infosConsultation));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    
    $authorization = "Authorization: Bearer ".$_SESSION["jwt"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    $resultat = curl_exec($ch);
    curl_close($ch);

    $resultat = json_decode($resultat, true);
    if (200 <= $resultat["statutCode"] && $resultat["statutCode"] < 300) {
        return $resultat["donnees"];
    } else {
        return -1;
    }
}