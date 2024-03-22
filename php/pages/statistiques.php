<?php session_start();
    require('fonctions.php');
    verifierAuthentification();

?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="style.css">
    <title> Statistiques </title>
</head>
<body>
    <?php include 'header.html' ?>
    
    <main class="main_affichage">
    <h1> Les statistiques </h1>
    
        <table class="tableFiltres">
            <tr>
                <th>Tranche d'âge</th>
                <th>Nombre d'hommes</th>
                <th>Nombre de femmes</th>
            </tr>
            <tr>
                <td>Moins de 25 ans</td>
                <td><?php echo $statsHommes['hommesMoins25'] ?></td>
                <td><?php echo $statsFemmes['femmesMoins25'] ?></td>
            </tr>   
            <tr>
                <td>Entre 25 et 50 ans</td>
                <td><?php echo $statsHommes['hommesEntre25et50'] ?></td>
                <td><?php echo $statsFemmes['femmesEntre25et50'] ?></td>
            </tr>    
            <tr>
                <td>Plus de 50 ans</td>
                <td><?php echo $statsHommes['hommesPlus50'] ?></td>
                <td><?php echo $statsFemmes['femmesPlus50'] ?></td>
            </tr>     
        </table>
        
        <br><br>
        <table id="table_affichage"> 
        <thead>
            <tr>
                <th>Civilite</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Durée totale des consultations</th>
            </tr>
        </thead><tbody>
    <?php
        
        while ($donnees = $reqDureeTotale->fetch()){
            echo '<tr>
                    <td>'.$donnees['civilite'].'</td>
                    <td>'.$donnees['nom'].'</td>
                    <td>'.$donnees['prenom'].'</td>
                    <td>'.$donnees['duree'].'</td>
                 </tr>';
        }
    ?>
    </tbody></table>
    </main>
</body>
</html>