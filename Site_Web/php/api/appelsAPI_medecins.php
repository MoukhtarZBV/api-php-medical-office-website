<?php

$urlAPI_medecins = "https://medical-office-ressources.alwaysdata.net/medecins";

function API_getMedecins(string | null $nom, string | null $prenom) : array | int {
    $url = $GLOBALS["urlAPI_medecins"] . ajouterParamsURL($nom, $prenom);

    $ch = curl_init();
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

function API_addMedecin(string $civilite, string $nom, string $prenom) : null | array {
    $url = $GLOBALS["urlAPI_medecins"];

    $infosMedecin = array(
        "civilite" => $civilite, 
        "nom" => $nom, 
        "prenom" => $prenom
    );
    $authorization = "Authorization: Bearer ".$_SESSION["jwt"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infosMedecin));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    $resultat = curl_exec($ch);
    curl_close($ch);

    if ($resultat) {
        $resultat = json_decode($resultat, true);
        return $resultat["donnees"];
    } else {
        return null;
    }
}

function ajouterParamsURL(string | null $nom, string | null $prenom) : string {
    $url = "";
    $premierFiltre = true;
    if (!empty($civilite)) {
        $url .= ($premierFiltre ? '?' : '&') . 'civilite=' . $civilite;
        $premierFiltre = false;
    }
    if (!empty($nom)) {
        $url .= ($premierFiltre ? '?' : '&') . 'nom=' . $nom;
        $premierFiltre = false;
    }
    if (!empty($prenom)) {
        $url .= ($premierFiltre ? '?' : '&') . 'prenom=' . $prenom;
    } 
    return $url;
}
