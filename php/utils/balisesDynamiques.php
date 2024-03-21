<?php

include_once('../api/appelsAPI_Medecins.php');
include_once('../api/appelsAPI_Usagers.php');

function creerComboboxUsagers($idUsager = null, $message = null) {
    $usagers = API_getUsagers(null, null);
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
    $medecins = API_getMedecins(null, null);
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

function construireTableUsager(array $usagers) : string {
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
                '<td>'.'<a href = \'modificationusager.php?idUsager='.$usager['medecinReferent'].'\'><img src="../images/modifier.png" alt=""width=30px></img></a></td>'.
                '<td>'.'<a href = \'suppression.php?id='.$usager['medecinReferent'].'&type=usager\'><img src="../images/supprimer.png" alt=""width=30px></img></a></td></tr>';
    }
    $table = $table . '</tbody></table></div>';
    return $table;
}

function construireTableMedecins(array $medecins) : string {
    // Créer l'entête de la table 
    $table ='<div class="conteneur_table_affichage">
                <table id="table_affichage">
                <thead>
                    <tr>
                        <th>Civilite </th>
                        <th>Nom </th>
                        <th>Prenom </th>
                    </tr>
            </thead><tbody>';
        
    // Créer le corps de la table 
    foreach ($medecins as $medecin){
        $table = $table . '<tr><td>'.$medecin['civilite'].'</td>'. 
                '<td>'.$medecin['nom'].'</td>'.
                '<td>'.$medecin['prenom'].'</td>'.                    
                '<td>'.'<a href = \'modificationMedecin.php?idMedecin='.$medecin['idMedecin'].'\'><img src="../images/modifier.png" alt="" width=30px></img></a>'.'</td>'.
                '<td>'.'<a href = \'suppression.php?id='.$medecin['idMedecin'].'&type=medecin\'><img src="../images/supprimer.png" alt="" width=30px></img></a>'.'</td>'.'</tr>';
    }
    $table = $table . '</tbody></table></div>';
    return $table;
}

function construireTableConsultations(array $consultations) : string {
    // Créer l'entête de la table 
    $table = '<div class="conteneur_table_affichage">
                    <table id="table_affichage">
                                <thead>
                                    <tr>
                                        <th>Médecin</th>
                                        <th>Patient</th>
                                        <th>Date de consultation</th>
                                        <th>Heure de consultation</th>
                                        <th>Durée de consultation</th>
                                    </tr>
                            </thead><tbody>';
                        
    // Créer le corps de la table 
    foreach ($consultations as $consultation) {
        $dateFormatee = formaterDate($consultation['dateConsultation']);
        $table = $table . '<tr><td>' . $consultation['nomMedecin'] . '</td>' .
            '<td>' . $consultation['nomUsager'] . '</td>' .
            '<td>' . $dateFormatee . '</td>' .
            '<td>' . str_replace(':', 'H', substr($consultation['heureDebut'], 0, 5)) . '</td>' .
            '<td>' . str_replace(':', 'H', substr($consultation['duree'], 0, 5)) . '</td>' .
            '<td>' . '<a href = \'modificationConsultation.php?id=' . $consultation['idConsultation'] . '\'><img src="../images/modifier.png" alt=""width=30px></a></td>' .
            '<td>' . '<a href = \'suppression.php?id=' . $consultation['idConsultation'] . '&type=consultation\'><img src="../images/supprimer.png" alt=""width=30px></a></td></tr>';
    }
    $table = $table . '</tbody></table></div>';
    return $table;
}