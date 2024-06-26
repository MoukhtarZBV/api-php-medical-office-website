<?php

$urlAPI_authentification = "https://medical-office.alwaysdata.net/api/API_auth.php";

function API_getToken(string $login, string $password) : string {
    $url = $GLOBALS["urlAPI_authentification"];

    $infosUtilisateur = array(
        "login" => $login, 
        "mdp" => $password
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infosUtilisateur));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultat = curl_exec($ch);
    curl_close($ch);
    echo $resultat;
    $resultat = json_decode($resultat, true);
    if (200 <= $resultat["statutCode"] && $resultat["statutCode"] < 300) {
        return $resultat["donnees"];
    } else {
        return -1;
    }
}
