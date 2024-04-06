<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_statistiques.php");
require("../db/connexionDB.php");
require("utilitairesAPI.php");

$pdo = createConnection();
$http_method = $_SERVER['REQUEST_METHOD'];

if ($http_method == "GET") {
    if (!empty($_GET["stat"])) {
        $type = $_GET["stat"];
        if ($type == "medecins") {
            $statsMedecins = getStatistiquesMedecins($pdo);
            if (!empty($statsMedecins)) {
                fournirReponse("Succes", 200, "Statistiques sur la durée totale des consultations des médecins récupérées", $statsMedecins);
            } else if (is_array($statsMedecins)) {
                fournirReponse("Erreur", 404, "Il n'existe pas de consultations pour établir des statistiques");
            } else {
                fournirReponse("Erreur", 400, "Erreur lors de la récupération des statistiques des médecins");
            }
        } else if ($type == "usagers") {
            if ($statsUsagers = getStatistiquesUsagers($pdo)) {
                fournirReponse("Succes", 200, "Statistiques sur le nombre d'usagers par tranche d'âge et sexe récupérées", $statsUsagers);
            } else {
                fournirReponse("Erreur", 400, "Erreur lors de la récupération des statistiques des usagers");
            }
        } else {
            fournirReponse("Erreur", 400, "Type de statistiques invalide (medecins ou usagers)");
        }
    } else {
        fournirReponse("Erreur", 400, "Type de statistiques non indiqué (medecins ou usagers)");
    }
}

