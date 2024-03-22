<?php 

function getStatistiquesUsagers(PDO $pdo) : bool | array {
    $reqHommes = 'SELECT
                    SUM(CASE WHEN civilite = \'M\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') < 25 THEN 1 ELSE 0 END) AS hommesMoins25,
                    SUM(CASE WHEN civilite = \'M\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') BETWEEN 25 AND 50 THEN 1 ELSE 0 END) AS hommesEntre25et50,
                    SUM(CASE WHEN civilite = \'M\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') > 50 THEN 1 ELSE 0 END) AS hommesPlus50
                    FROM usager';
    $reqFemmes = 'SELECT
                    SUM(CASE WHEN civilite = \'Mme\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') < 25 THEN 1 ELSE 0 END) AS femmesMoins25,
                    SUM(CASE WHEN civilite = \'Mme\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') BETWEEN 25 AND 50 THEN 1 ELSE 0 END) AS femmesEntre25et50,
                    SUM(CASE WHEN civilite = \'Mme\' AND DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), dateNaissance)), \'%Y\') > 50 THEN 1 ELSE 0 END) AS femmesPlus50
                    FROM usager';

    if (!($statsHommes = $pdo->query($reqHommes)) || !($statsFemmes = $pdo->query($reqFemmes))) {
        return false;
    }
    return array_merge($statsHommes->fetch(), $statsFemmes->fetch());
}

function getStatistiquesMedecins(PDO $pdo) : bool | array {
    $reqDureeTotaleMedecins = 'SELECT civilite, nom, prenom, TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(duree))), \'%kh%i\') as duree 
                                    FROM medecin m, consultation c 
                                    WHERE m.idMedecin = c.idMedecin 
                                    GROUP BY nom, prenom, civilite';

    if ($resDureeTotaleMedecin = $pdo->query($reqDureeTotaleMedecins)) {
        return false;
    }
    return $resDureeTotaleMedecin->fetchAll(PDO::FETCH_ASSOC);
}