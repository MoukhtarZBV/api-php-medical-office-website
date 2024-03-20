<?php

require_once('../utils/fonctions.php');

function getConsultations(string | null $idMedecin, string | null $idUsager, string | null $date) : array | int {
    $url = $GLOBALS["urlAPIConsultations"];

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
   
    $resultat = curl_exec($ch);
    curl_close($ch);
    $resultat = json_decode($resultat, true);
    return returnStatut($resultat["statutCode"], $resultat["donnees"]);
}