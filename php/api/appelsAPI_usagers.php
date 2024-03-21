<?php

$urlAPI_usagers = "http://localhost/api-php-medical-office-website/php/api/API_usagers.php";

function API_getUsagers(string | null $nom, string | null $prenom) : array | int {
    $url = $GLOBALS["urlAPI_usagers"] . ajouterParamsURL1($nom, $prenom);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultat = curl_exec($ch);
    curl_close($ch);
    
    $resultat = json_decode($resultat, true);
    if (200 <= $resultat["statutCode"] && $resultat["statutCode"] < 300) {
        return $resultat["donnees"];
    } else {
        return -1;
    }
}

function ajouterParamsURL1(string | null $nom, string | null $prenom) : string {
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