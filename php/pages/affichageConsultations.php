<?php session_start();
    require('fonctions.php');
    verifierAuthentification();
    $pdo = creerConnexion();

    $idMedecin = '';
    $idUsager = '';
    if (isset($_POST["valider"])) {
        $idMedecin = $_POST["idMedecin"];
        $idUsager = $_POST["idUsager"];
        $date = $_POST["date"];
    }

    // On affiche toutes les lignes renvoyées ou un message si rien n'a été trouvé
    $table = '';
    $nombreLignes = '';
    if ($stmt->rowCount() > 0) {
        $nombreLignes = '<div class="nombre_lignes"><strong>' . $stmt->rowCount() . '</strong> consultation(s) trouvée(s)</div>';
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
        while ($dataConsultation = $stmt->fetch()) {
            $dateFormatee = formaterDate($dataConsultation['dateConsultation']);
            $table = $table . '<tr><td>' . $dataConsultation['nomMed'] . '</td>' .
                '<td>' . $dataConsultation['nomUsager'] . '</td>' .
                '<td>' . $dateFormatee . '</td>' .
                '<td>' . str_replace(':', 'H', substr($dataConsultation['heureDebut'], 0, 5)) . '</td>' .
                '<td>' . str_replace(':', 'H', substr($dataConsultation['duree'], 0, 5)) . '</td>' .
                '<td>' . '<a href = \'modificationConsultation.php?id=' . $dataConsultation['cle'] . '\'><img src="Images/modifier.png" alt=""width=30px></a></td>' .
                '<td>' . '<a href = \'suppression.php?id=' . $dataConsultation['cle'] . '&type=consultation\'><img src="Images/supprimer.png" alt=""width=30px></a></td></tr>';
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
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="style.css">
    <title> Consultations </title>
</head>

<body>
    <?php include 'header.html' ?>
    
    <main class="main_affichage">
        <h1> Liste des consultations </h1>

        <form class="formulaire_table" method="post" action="affichageConsultations.php">
            <div class="colonne_formulaire large">
                Médecin <?php creerComboboxMedecins($pdo, $idMedecin, 'Tous les médecins'); ?>
            </div>
            <div class="colonne_formulaire large">
                Patient 
                    <?php creerComboboxUsagers($pdo, $idUsager, 'Tous les usagers'); ?>
            </div>
            <div class="colonne_formulaire petit">
                Date consultation <input type="date" name="date" value="<?php echo $date ?>">
            </div>
            <div class="conteneur_boutons">
                <input type="submit" value="Rechercher" name="valider">
                <a href="ajoutConsultation.php" class="lien_ajouter">
                    <div class="bouton_ajouter"><img src="Images/ajouter.png" width="20px"/>Ajouter</div>
                </a>
            </div>
        </form>
        <?php echo $nombreLignes; if (!empty($table)) { echo $table; } ?>
    </main>
</body>
</html>