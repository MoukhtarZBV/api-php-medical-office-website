<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_statistiques.php");
require("utilitairesAPI.php");

$pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_ressources", '350740', '$iutinfo');
$http_method = $_SERVER['REQUEST_METHOD'];
if ($http_method == "GET") {
    if (!empty($_GET["type"])) {
        $type = $_GET["type"];
        if ($type == "medecins") {
            if ($statsMedecins = getStatistiquesMedecins($pdo)) {
                fournirReponse("Succes", 200, "Statistiques sur la durée totale des consultations des médecins récupérées", $statsMedecins);
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

