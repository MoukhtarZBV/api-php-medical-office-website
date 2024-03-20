<?php

require_once('../api/appelsAPI/appelsAPI_Medecins.php');
require_once('../api/appelsAPI/appelsAPI_Usagers.php');

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

function afficherTableUsager(array $usagers) : string {
    // Créer l'entête de la table 
    $table = '<div class="conteneur_table_affichage">
                <table id="table_affichage">
                <thead>
                    <tr>
                        <th>Civilite </th>
                        <th>Nom </th>
                        <th>Prenom </th>
                        <th>Adresse </th>
                        <th>Ville </th>
                        <th>Code postal </th>
                        <th>Numéro sécurité sociale </th>
                        <th>Date de naissance </th>
                        <th>Ville de naissance </th>
                        <th>Médecin référent </th>
                    </tr>
                </thead><tbody>';
    
    // Créer le corps de la table 
    foreach ($usagers as $usager){
        $dateFormatee = formaterDate($usager['dateNaissance']);
        $table .= '<tr><td>'.$usager['civilite'].'</td>'.
                '<td>'.$usager['nom'].'</td>'.
                '<td>'.$usager['prenom'].'</td>'.                          
                '<td>'.$usager['adresse'].'</td>'.
                '<td>'.$usager['ville'].'</td>'.
                '<td>'.$usager['codePostal'].'</td>'.
                '<td>'.$usager['numeroSecuriteSociale'].'</td>'.
                '<td>'.$dateFormatee.'</td>'.
                '<td>'.$usager['lieuNaissance'].'</td>'.
                '<td>'.$usager['nomMedecin'].' '.$usager['prenomMedecin'].'</td>'.
                '<td>'.'<a href = \'modificationusager.php?idUsager='.$usager['medecinReferent'].'\'><img src="../../images/modifier.png" alt=""width=30px></img></a></td>'.
                '<td>'.'<a href = \'suppression.php?id='.$usager['medecinReferent'].'&type=usager\'><img src="../../images/supprimer.png" alt=""width=30px></img></a></td></tr>';
    }
    $table = $table . '</tbody></table></div>';
    return $table;
}