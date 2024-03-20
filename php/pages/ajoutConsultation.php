<?php session_start();
    require('../functions/fonctionsVerifierInputs.php');
    require('../db/DAO_consultations.php');
    require('../connexionDB.php');
    verifierAuthentification();
    $pdo = createConnection();

    $popup = '';
    if (!empty($_POST["Confirmer"])) {
        $today = gmdate('Y-m-d', time());
        $idMedecin = $_POST['idMedecin'];
        $idUsager = $_POST['idUsager'];
        $date = $_POST['date'];
        $heure = $_POST['heureD'];
        $duree = $_POST['duree'];

        $message = '';
        $classeMessage = '';
        if (!dateApresLe($date, $today)) {
            $message = 'La date de la consultation ne peut pas être infèrieure à la date du jour';
            $classeMessage = 'erreur';
        } else if (!heureApres8HeureAvant20Heure($heure)) {
            $message = 'La consultation doit avoir lieu entre 8 heures et 20 heures';
            $classeMessage = 'erreur';
        } else if (!dureeSuperieure15MinutesInferieur60Minutes($duree)) {
            $message = 'La consultation doit durer entre 5 minutes et une heure';
            $classeMessage = 'erreur';
        } else {
            $consulationsChevauchantes = horaireChevauchantePourMedecin($pdo, $date, $heure, $duree, $idMedecin);

            if (!$consulationsChevauchantes) {
                addConsultation($pdo, $idMedecin, $date, $heure, $duree, $idUsager);
                $dateFormatee = formaterDate($date);
                $message = 'La consultation du <strong>' . $dateFormatee . '</strong> à <strong>' . str_replace(':', 'H', $heure) . '</strong> a été ajoutée !';
                $classeMessage = 'succes';
            } else {
                $message = 'La consultation chevauche avec un autre créneau pour ce médecin';
                $classeMessage = 'erreur';
            }
        }

        // Création de la popup d'erreur ou de succés
        $popup = '<div class="popup ' . $classeMessage . '">' . $message .'</div>';
    
    }
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="style.css">
    <title> Planification d'une consultation </title>
</head>

<body id="body_fond">
    <?php include 'header.html' ?>

    <?php if (!empty($popup)) { echo $popup; } ?>

    <div class="titre_formulaire">
        <h1> Planification d'une consultation </h1>
    </div>

    <form class="formulaire" action="ajoutConsultation.php" method="post">

        <?php
        echo 'Médecin ';
        echo creerComboboxMedecins(null, null);
        echo 'Usager ';
        echo creerComboboxUsagers(null, null); 
        ?>
        <div class="ligne_formulaire temps_consultation">
            <div class="colonne_formulaire moitie">
                Date de consultation <input type="date" name="date" value="" min="<?php echo $today ?>" required>
            </div>
            <div class="colonne_formulaire moitie">
                Horaire de consultation <input type="time" name="heureD" min="08:00" max="20:00" value="08:00" required>
            </div>
            <div class="colonne_formulaire petit">
                Durée de consultation <input type="time" name="duree" min="00:05" max="02:00" value="00:30" required>
            </div>
        </div>
        <div class="conteneur_boutons">
            <input type="reset" name="Vider" value="Vider">
            <input type="submit" name="Confirmer" value="Confirmer">
        </div>
    </form>
    <!-- Script pour sélectionner automatiquement le médecin référent -->
    <script src="selection-medecin-ref.js"></script>
</body>
</html>