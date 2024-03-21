<?php session_start();
    require('../api/appelsAPI_medecins.php');
    require('../utils/balisesDynamiques.php');
    require('../utils/utilitaires.php');
    verifierAuthentification();

    // Récupération des champs qui ont été saisis
    $nom = $_POST["nom"] ?? null;
    $prenom = $_POST["prenom"] ?? null;

    // Appel à l'API pour récupérer les médecins, filtrés ou non
    $medecins = API_getMedecins($nom, $prenom);

    // Affichage de toutes les médecins récupérés
    if ($medecins) {
        $nombreLignes ='<div class="nombre_lignes"><strong>'. count($medecins) .'</strong> médecin(s) trouvé(s)</div>';
        $table = construireTableMedecins($medecins);
    } else {
        $nombreLignes = '<div class="nombre_lignes" style="color: red;"><strong>Aucun</strong> médecin trouvé</div>';
    }
?>
<!DOCTYPE HTML> 
<html>

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/header.css">
    <title> Médecins </title>
</head>
<body>
    <?php include '../../header.html' ?>
    
    <main class="main_affichage">
        <h1> Liste des médecins </h1>
        <div class="conteneur_table_recherche">
            <form method="post" action="#" class="formulaire_table">
                <div class="colonne_formulaire large">
                    Nom <input type="text" name="nom" value="<?php if (!empty($nom)) echo $nom; ?>">
                </div>
                <div class="colonne_formulaire large">
                    Prénom <input type="text" name="prenom" value="<?php if (!empty($prenom)) echo $prenom; ?>">
                </div>
                <div class="conteneur_boutons">
                    <input type="submit" value="Rechercher" name="valider">
                    <a href="ajoutMedecin.php" class="lien_ajouter">
                        <div class="bouton_ajouter"><img src="Images/ajouter.png" width="20px"/>Ajouter</div>
                    </a>
                </div>
            </form>
            <?php echo $nombreLignes; if (!empty($table)) { echo $table; } ?>
        </div>
    </main>
</body>

</html>