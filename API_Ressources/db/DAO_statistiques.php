<?php 

function getStatistiquesUsagers(PDO $pdo) : bool | array {
    $reqHommes = 'SELECT
                    SUM(CASE WHEN sexe = \'H\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') < 25 THEN 1 ELSE 0 END) AS hommesMoins25,
                    SUM(CASE WHEN sexe = \'H\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') BETWEEN 25 AND 50 THEN 1 ELSE 0 END) AS hommesEntre25et50,
                    SUM(CASE WHEN sexe = \'H\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') > 50 THEN 1 ELSE 0 END) AS hommesPlus50
                    FROM usager';
    $reqFemmes = 'SELECT
                    SUM(CASE WHEN sexe = \'F\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') < 25 THEN 1 ELSE 0 END) AS femmesMoins25,
                    SUM(CASE WHEN sexe = \'F\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') BETWEEN 25 AND 50 THEN 1 ELSE 0 END) AS femmesEntre25et50,
                    SUM(CASE WHEN sexe = \'F\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') > 50 THEN 1 ELSE 0 END) AS femmesPlus50
                    FROM usager';

    if (!($statsHommes = $pdo->query($reqHommes)) || !($statsFemmes = $pdo->query($reqFemmes))) {
        return false;
    }
    return array_merge($statsHommes->fetch(PDO::FETCH_ASSOC), $statsFemmes->fetch(PDO::FETCH_ASSOC));
}

function getStatistiquesMedecins(PDO $pdo) : bool | array {
    $reqDureeTotaleMedecins = 'SELECT civilite, nom, prenom, CONCAT(FLOOR(SUM(duree) / 60), \'h\', LPAD(SUM(duree) % 60, 2, \'0\')) as duree 
                                    FROM medecin m, consultation c 
                                    WHERE m.idMedecin = c.idMedecin 
                                    GROUP BY nom, prenom, civilite';

    if (!($resDureeTotaleMedecin = $pdo->query($reqDureeTotaleMedecins))) {
        return false;
    }
    return $resDureeTotaleMedecin->fetchAll(PDO::FETCH_ASSOC);
}