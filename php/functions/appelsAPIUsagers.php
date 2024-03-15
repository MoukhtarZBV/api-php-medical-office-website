<?php

require_once('fonctions.php');

function API_getUsagers(string | null $civilite, string | null $nom, string | null $prenom) : array | int {
    $url = $GLOBALS["urlAPIUsagers"];
    $url .= ajouterParamsURL($civilite, $nom, $prenom);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultat = curl_exec($ch);
    curl_close($ch);
    
    $resultat = json_decode($resultat, true);
    return returnStatut($resultat["statutCode"], $resultat["donnees"]);
}