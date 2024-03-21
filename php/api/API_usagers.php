<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_usagers.php");
require("utilitairesAPI.php");

$pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_ressources", '350740', '$iutinfo');
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method) {

    case "GET":

        //vérification de si l'id de l'usager a été spécifié
        if (isset($_GET["id"])) {

            try {

                //récupération de l'usager et traitement en fonction du résultat
                $id = $_GET["id"];
                $usager = getUsagerById($pdo, $id);

                if (is_null($usager)) {                                     //il y a eu une erreur
                    fournirReponse("Erreur", 400, "Une erreur est survenue : l'usager d'id ".$id." n'a pas pu être récupéré");
                } else if (!empty($usager = getUsagerById($pdo, $id))) {    //un usager a été trouvé
                    fournirReponse("Succes", 200, "Usager d'ID ".$id." récuperé", $usager);
                } else {                                                    //aucune erreur mais aucun usager trouvé
                    fournirReponse("Erreur", 404, "Usager d'ID ".$id." inexistant");
                }

            } catch (TypeError $error) {
                //en cas d'une erreur de type TypeError levée, renvoyer un message
                //d'erreur spécifiant que l'identifiant doit être un ENTIER
                fournirReponse("Erreur", 422, "L'identifiant fourni doit être un entier");
            }

        //si l'id n'a pas été spécifié, vérifie si des arguments de recherche l'ont été

        //si aucun ne l'a été, récupération de tous les usagers : 
        } else if (!isset($_GET["civilite"]) && !isset($_GET["nom"]) && 
            !isset($_GET["prenom"]) && !isset($_GET["numeroSecuriteSociale"])) {

            //récupération de tous les usagers et traitement en fonction du résultat
            $usagers = getUsagers($pdo, null, null, null, null);

            if (is_null($usagers)) {                                                    //s'il y a eu une erreur
                fournirReponse("Erreur",400,"Une erreur est survenue : les usagers n'ont pas pu être récupérés");
            } else if (!empty($usagers = getUsagers($pdo, null, null, null, null))) {   //si on a trouvé des usagers
                fournirReponse("Succes", 200, "Tous les usagers récuperés", $usagers);  
            } else {                                                                    //s'il n'y a pas eu d'erreur mais qu'on a trouvé aucun usager
                fournirReponse("Erreur", 404, "Aucun usager trouvé");
            }

        //si au moins un critère de recherche a été spécifié, recherche par filtrage
        } else {

            //récupération des critères de recherche 
            $civilite = isset($_GET["civilite"]) ? $_GET["civilite"] : null;
            $nom = isset($_GET["nom"]) ? $_GET["nom"] : null;
            $prenom = isset($_GET["prenom"]) ? $_GET["prenom"] : null;
            $numSS = isset($_GET["numeroSecuriteSociale"]) ? $_GET["numeroSecuriteSociale"] : null;

            //exécution de la requête et traitement en fonction du résultat 
            $usagersFiltres = getUsagers($pdo, $civilite, $nom, $prenom, $numSS);

            if (is_null($usagersFiltres)) {         //s'il y a eu une erreur
                fournirReponse("Erreur", 400, "Une erreur est survenue : les usagers fitrés n'ont pas pu être récupérés");
            } else if (!empty($usagersFiltres)) {   //si des usagers ont été récupérés
                fournirReponse("Succes", 200, "Usagers filtrés récuperés", $usagersFiltres);
            } else {                                //s'il n'y a eu aucune erreur mais aucun usager trouvé
                fournirReponse("Erreur", 404, "Aucun usager trouvé correspondant à vos filtres");
            }

        }

        break;

    case "POST":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {

            $contenuFichier = file_get_contents('php://input');
            $arguments = json_decode($contenuFichier, true); 

            //vérification de la présence de tous les champs NECESSAIRES
            if (!empty($arguments["civilite"]) && !empty($arguments["nom"]) &&
                !empty($arguments["prenom"]) && !empty($arguments["adresse"]) &&
                !empty($arguments["ville"]) && !empty($arguments["codePostal"]) &&
                !empty($arguments["numeroSecuriteSociale"]) && !empty($arguments["dateNaissance"]) &&
                !empty($arguments["lieuNaissance"])) {

                    //recupération d'un possible champ facultatif
                    $medRef = $arguments["medecinReferent"] ?? null;

                    //vérification du succès de l'insertion
                    if ($id = addUsager($pdo, $arguments["civilite"], $arguments["nom"], $arguments["prenom"], 
                    $arguments["adresse"], $arguments["ville"], $arguments["codePostal"], $arguments["numeroSecuriteSociale"],
                    $arguments["dateNaissance"], $arguments["lieuNaissance"], $medRef)) {
                        $usager = getUsagerById($pdo, $id);
                        fournirReponse("Succes", 200, "Usager créée", [$usager]);
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue : l'usager n'a pas été créé");
                    }

            } else {
                fournirReponse("Erreur", 422, "Création d'usager impossible, tous les champs ne sont pas renseignés");
            }

        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }

        break;

    case "DELETE":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {

            //vérification de la présence de l'identifiant dans la requête 
            if (!empty($_GET["id"])) {

                try {

                    $result = deleteUsager($pdo, $_GET["id"]);

                    if (is_null($result)) {
                        fournirReponse("Erreur", 400, "Une erreur est survenue et l'usager n'a pas été supprimé");
                    } else if (!empty($result)) {
                        fournirReponse("Succes", 200, "Usager n°".$_GET["id"]." supprimé");
                    } else {
                        fournirReponse("Erreur", 400, "Suppression impossible : l'usager n°".$_GET["id"]." n'existe pas");
                    }

                } catch (TypeError $error) {
                    fournirReponse("Erreur", 422, "L'identifiant fourni doit être un entier");
                }

            } else {
                fournirReponse("Erreur", 422, "Identifiant de l'usager non renseigné");
            }

        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }

        break;

    case "PATCH":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {

            $postedData = file_get_contents('php://input');
            $arguments = json_decode($postedData,true); 

            //vérification de la présence d'au moins un paramètre de modif 
            if (!empty($arguments["civilite"]) || !empty($arguments["nom"]) ||
                !empty($arguments["prenom"]) || !empty($arguments["adresse"]) ||
                !empty($arguments["ville"]) || !empty($arguments["codePostal"]) ||
                !empty($arguments["numeroSecuriteSociale"]) || !empty($arguments["dateNaissance"]) || 
                !empty($arguments["lieuNaissance"]) || !empty($arguments["medecinReferent"])) {

                $id = $_GET["id"];

                $civ = isset($arguments["civilite"]) ? $arguments["civilite"] : null;
                $nom = isset($arguments["nom"]) ? $arguments["nom"] : null;
                $prenom = isset($arguments["prenom"]) ? $arguments["prenom"] : null;
                $adresse = isset($arguments["adresse"]) ? $arguments["adresse"] : null;
                $ville = isset($arguments["ville"]) ? $arguments["ville"] : null;
                $codePostal = isset($arguments["codePostal"]) ? $arguments["codePostal"] : null;
                $numeroSecuriteSociale = isset($arguments["numeroSecuriteSociale"]) ? $arguments["numeroSecuriteSociale"] : null;
                $dateNaissance = isset($arguments["dateNaissance"]) ? $arguments["dateNaissance"] : null;
                $lieuNaissance = isset($arguments["lieuNaissance"]) ? $arguments["lieuNaissance"] : null;
                $medRef = isset($arguments["medecinReferent"]) ? $arguments["medecinReferent"] : null;

                if (editUsager($pdo, $id, $civ, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale,
                $dateNaissance, $lieuNaissance, $medRef)) {
                    $usager = getUsagerById($pdo, $id);
                    fournirReponse("Succes", 200, "Usager d'ID : ".$id." modifié", $usager);
                } else {
                    fournirReponse("Erreur", 400, "Usager d'ID : ".$id." inchangée");
                }

            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 422, "Identifiant de l'usager non spécifié");
            } else {
                fournirReponse("Erreur", 422, "Au moins un paramètre doit être saisi");
            }
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }
        break;

    case "PUT":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {

            $postedData = file_get_contents('php://input');
            $arguments = json_decode($postedData,true); 

            if (!(empty($arguments["civilite"]) || empty($arguments["nom"]) ||
            empty($arguments["prenom"]) || empty($arguments["adresse"]) ||
            empty($arguments["ville"]) || empty($arguments["codePostal"]) ||
            empty($arguments["numeroSecuriteSociale"]) || empty($arguments["dateNaissance"]) || 
            empty($arguments["lieuNaissance"])) && !empty($_GET["id"])) {

            $id = $_GET["id"]; $medRef = $arguments["medecinReferent"] ?? null;

            $civ = isset($arguments["civilite"]) ? $arguments["civilite"] : null;
            $nom = isset($arguments["nom"]) ? $arguments["nom"] : null;
            $prenom = isset($arguments["prenom"]) ? $arguments["prenom"] : null;
            $adresse = isset($arguments["adresse"]) ? $arguments["adresse"] : null;
            $ville = isset($arguments["ville"]) ? $arguments["ville"] : null;
            $codePostal = isset($arguments["codePostal"]) ? $arguments["codePostal"] : null;
            $numeroSecuriteSociale = isset($arguments["numeroSecuriteSociale"]) ? $arguments["numeroSecuriteSociale"] : null;
            $dateNaissance = isset($arguments["dateNaissance"]) ? $arguments["dateNaissance"] : null;
            $lieuNaissance = isset($arguments["lieuNaissance"]) ? $arguments["lieuNaissance"] : null;
            $medRef = isset($arguments["medecinReferent"]) ? $arguments["medecinReferent"] : null;

            if (editUsager($pdo, $id, $civ, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale,
            $dateNaissance, $lieuNaissance, $medRef)) {
                $usager = getUsagerById($pdo, $id);
                fournirReponse("Succes", 200, "Usager d'ID : ".$id." modifié", $usager);
            } else {
                fournirReponse("Erreur", 400, "Usager d'ID : ".$id." inchangée");
            }

        } else if (empty($_GET["id"])) {
            fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
        } else {
            fournirReponse("Erreur", 422, "Tous les paramètres doivent être saisis dans un PUT.");
        }

    } else {
        fournirReponse("Erreur", 401, "Jeton invalide");
    }
    break;

}

?>