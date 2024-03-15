<?php

require_once('appelsAPIMedecins.php');
require_once('appelsAPIUsagers.php');

function creerComboboxUsagers($idUsager = null, $message = null) {
    $usagers = API_getUsagers(null, null, null);
    echo '<select name="idUsager" id="combobox_usagers">';
    if ($message != null){
        echo '<option value="">' . $message . '</option>';
    }
    foreach ($usagers as $usager) {
        $id = $usager["idUsager"];
        $titre = str_pad($usager["civilite"] . '. ', 5, ' ') . $usager["nom"] . ' ' . $usager["prenom"] . ' (' . $usager["numeroSecuriteSociale"] . ')';
        $selected = $idUsager == $id ? 'selected' : '';
        echo '<option value=' . $id . ' ' . $selected . ' data-idMedecinRef=' . $usager["medecinReferent"] . '> ' . $titre . '</option>';
    }
    echo '</select>';
} 

function creerComboboxMedecins($idMedecin = null, $message = null) {
    $medecins = API_getMedecins(null, null, null);
    echo '<select name="idMedecin" id="combobox_medecins">';
    if ($message != null){
        echo '<option value="">' . $message . '</option>';
    }
    foreach ($medecins as $medecin) {
        $id = $medecin["idMedecin"];
        $titre = $medecin["civilite"] . '. ' . $medecin["nom"] . ' ' . $medecin["prenom"];
        $selected = $idMedecin == $id ? 'selected' : '';
        echo '<option value=' . $id . ' ' . $selected . '> ' . $titre . '</option>';
    }
    echo '</select>';
} 