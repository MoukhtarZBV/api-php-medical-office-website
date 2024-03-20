<?php session_start();
    require('../api/appelsAPI/appelsAPI_Consultations.php');
    require('../utils/balisesDynamiques.php');
    verifierAuthentification();
    
    // Récupération des champs qui ont été saisis
    $idMedecin = $_POST["idMedecin"] ?? null;
    $idUsager = $_POST["idUsager"] ?? null;
    $date = $_POST["date"] ?? null;

    // Appel à l'API pour récupérer les consultations, filtrées ou non
    $consultations = getConsultations($idMedecin, $idUsager, $date);

    // Affichage de toutes les consultations récupérées
    if ($consultations) {
        $nombreLignes = '<div class="nombre_lignes"><strong>' . count($consultations) . '</strong> consultation(s) trouvée(s)</div>';
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
        foreach ($consultations as $consultation) {
            $dateFormatee = formaterDate($consultation['dateConsultation']);
            $table = $table . '<tr><td>' . $consultation['nomMedecin'] . '</td>' .
                '<td>' . $consultation['nomUsager'] . '</td>' .
                '<td>' . $dateFormatee . '</td>' .
                '<td>' . str_replace(':', 'H', substr($consultation['heureDebut'], 0, 5)) . '</td>' .
                '<td>' . str_replace(':', 'H', substr($consultation['duree'], 0, 5)) . '</td>' .
                '<td>' . '<a href = \'modificationConsultation.php?id=' . $consultation['idConsultation'] . '\'><img src="Images/modifier.png" alt=""width=30px></a></td>' .
                '<td>' . '<a href = \'suppression.php?id=' . $consultation['idConsultation'] . '&type=consultation\'><img src="Images/supprimer.png" alt=""width=30px></a></td></tr>';
        }
        $table = $table . '</tbody></table></div>';
    } else {
        $nombreLignes = '<div class="nombre_lignes" style="color: red;"><strong>Aucune</strong> consultation trouvée</div>';
    }
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/style.css">
    <title> Consultations </title>
</head>

<body>
    <?php include '../../header.html' ?>
    
    <main class="main_affichage">
        <h1> Liste des consultations </h1>

        <form class="formulaire_table" method="post" action="#">
            <div class="colonne_formulaire large">
                Médecin <?php creerComboboxMedecins($idMedecin, 'Tous les médecins'); ?>
            </div>
            <div class="colonne_formulaire large">
                Patient 
                    <?php creerComboboxUsagers($idUsager, 'Tous les usagers'); ?>
            </div>
            <div class="colonne_formulaire petit">
                Date consultation <input type="date" name="date" value="<?php echo $date ?>">
            </div>
            <div class="conteneur_boutons">
                <input type="submit" value="Rechercher" name="valider">
                <a href="ajoutConsultation.php" class="lien_ajouter">
                    <div class="bouton_ajouter"><img src="../../images/ajouter.png" width="20px"/>Ajouter</div>
                </a>
            </div>
        </form>
        <!-- Affichage des consultations trouvées, avec le nombre de consultations -->
        <?php echo $nombreLignes; if (!empty($table)) { echo $table; } ?>
    </main>
</body>
</html>