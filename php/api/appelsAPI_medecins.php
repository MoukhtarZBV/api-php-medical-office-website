<?php

$urlAPI_medecins = "http://localhost/api-php-medical-office-website/php/api/API_medecins.php";

function API_getMedecins(string | null $nom, string | null $prenom) : array | int {
    $url = $GLOBALS["urlAPI_medecins"] . ajouterParamsURL($nom, $prenom);

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

function API_addMedecin(string $civilite, string $nom, string $prenom) : bool | array {
    $url = $GLOBALS["urlAPI_consultations"];

    $infosConsultation = array(
        "civilite" => $civilite, 
        "nom" => $nom, 
        "prenom" => $prenom
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infosConsultation));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultat = curl_exec($ch);
    curl_close($ch);

    $resultat = json_decode($resultat, true);
    return $resultat["donnees"];
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

