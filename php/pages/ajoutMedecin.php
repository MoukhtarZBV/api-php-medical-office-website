<?php session_start();
    require('../api/appelsAPI_medecins.php');
    require('../utils/fonctionsVerifierInputs.php');
    require('../utils/balisesDynamiques.php');
    require('../utils/utilitaires.php');
    verifierAuthentification();

    $popup = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Confirmer"])) {
        $civilite = $_POST['civ'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];

        $message = '';
        $classeMessage = '';
        if (!inputSansEspacesCorrect($nom, TAILLE_NOM)){
            $message = 'Le nom n\'est pas correctement saisi';
            $classeMessage = 'erreur';
        } else if (!inputSansEspacesCorrect($prenom, TAILLE_PRENOM)){
            $message = 'Le prénom n\'est pas correctement saisi';
            $classeMessage = 'erreur';
        } else {
            if ($medecin = API_addMedecin($civilite, $nom, $prenom)) {
                $message = 'Le médecin <strong>' . $civilite . ' ' . $nom . ' ' . $prenom . '</strong> a été ajouté !';
                $classeMessage = 'succes';
            } else {
                $message = 'Une erreur s\'est produite lors de l\'ajout du médecin <strong>' . $civilite . ' ' . $nom . ' ' . $prenom . '</strong>';
                $classeMessage = 'erreur';
            }
        }

        // Affichage de la popup d'erreur ou de succés
        if (!empty($message)){
            $popup = '<div class="popup ' . $classeMessage . '">' . $message .'</div>';
        }
    }
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title> Ajout d'un médecin </title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/header.css">
</head>

<body id='body_fond'>
    <?php include '../../header.html' ?>

    <?php if (!empty($popup)) { echo $popup; } ?>

    <div class="titre_formulaire">
        <h1>Ajout d'un médecin</h1>
    </div>

    <form class="formulaire" action="ajoutMedecin.php" method="post">

        <div class="conteneur_civilite">
            Civilité
            <div class="choix_civilite">
                <input type="radio" id="civM" name="civ" value="M" checked />
                <label for="civM">M</label>
                <img src="../../images/homme.png" alt="Homme" class="image_civilite">
            </div>
            <div class="choix_civilite">
                <input type="radio" id="civMme" name="civ" value="Mme" />
                <label for="civMme">Mme</label>
                <img src="../../images/femme.png" alt="Femme" class="image_civilite">
            </div>
        </div>
        <div class="ligne_formulaire">
            <div class="colonne_formulaire moitie">
                Nom <input type="text" name="nom" value="" maxlength=50 required>
            </div>
            <div class="colonne_formulaire moitie">
                Prénom <input type="text" name="prenom" value="" maxlength=50 required>
            </div>
        </div>
        <div class="conteneur_boutons">
            <input type="reset" name="Vider" value="Vider">
            <input type="submit" name="Confirmer" value="Confirmer">
        </div>
    </form>
    <!-- Script pour formater les inputs -->
    <script src="format-texte-input.js"></script>
</body>

</html>