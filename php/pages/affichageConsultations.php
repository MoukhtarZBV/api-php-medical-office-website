<?php session_start();
    require('../api/appelsAPI_Consultations.php');
    require('../utils/balisesDynamiques.php');
    require('../utils/utilitaires.php');
    verifierAuthentification();
    
    // Récupération des champs qui ont été saisis
    $idMedecin = $_POST["idMedecin"] ?? null;
    $idUsager = $_POST["idUsager"] ?? null;
    $date = $_POST["date"] ?? null;

    // Appel à l'API pour récupérer les consultations, filtrées ou non
    $consultations = API_getConsultations($idMedecin, $idUsager, $date);

    // Affichage de toutes les consultations récupérées
    if ($consultations) {
        $nombreLignes = '<div class="nombre_lignes"><strong>' . count($consultations) . '</strong> consultation(s) trouvée(s)</div>';
        $table = construireTableConsultations($consultations);
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