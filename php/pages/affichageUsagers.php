<?php session_start();
    require('../api/appelsAPI/appelsAPI_usagers.php');
    require('../utils/balisesDynamiques.php');
    verifierAuthentification();

    // Récupération des champs qui ont été saisis
    $nom = $_POST["nom"] ?? null;
    $prenom = $_POST["prenom"] ?? null;

    // Appel à l'API pour récupérer les usagers, filtrés ou non
    $usagers = API_getUsagers($nom, $prenom);

    // Affichage de toutes les usagers récupérés
    if ($usagers) {
        $nombreLignes = '<div class="nombre_lignes"><strong>'. count($usagers) .'</strong> usager(s) trouvé(s)</div>';
        $table = afficherTableUsager($usagers);
    } else {
        $nombreLignes = '<div class="nombre_lignes" style="color: red;"><strong>Aucun</strong> usager trouvé</div>';
    }
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/header.css">
    <title> Usagers </title>
</head>

<body>
    <?php include '../../header.html' ?>
    
    <main class="main_affichage">
        <h1> Liste des usagers </h1>
        <div class="conteneur_table_recherche">
            <form method="post" action="#" class="formulaire_table">
                <input type="text" name="criteres" class="espaces_permis" placeholder="Entrez des mots-clés séparés par un espace" value="<?php if (isset($_POST['criteres'])) echo $_POST['criteres'] ?>">
                <input type="submit" value="Rechercher">
                <a href="ajoutusager.php" class="lien_ajouter">
                    <div class="bouton_ajouter"><img src="Images/ajouter.png" width="20px"/>Ajouter</div>
                </a>
            </form>
            <?php echo $nombreLignes; if (!empty($table)) { echo $table; } ?>
        </div>
    </main>
    <!-- Script pour formater les inputs -->
    <script src="format-texte-input.js"></script>
</body>
</html>