<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAOconsultations.php");
require("utilitairesAPI.php");

$pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_ressources", '350740', '$iutinfo');
$http_method = $_SERVER['REQUEST_METHOD'];

switch ($http_method) {

    case "GET":

        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            if (!empty($consultation = getConsultationById($pdo, $id))) {
                fournirReponse("Succes", 200, "Consultation d'ID : ".$id." récuperée", $consultation);
            } else {
                fournirReponse("Erreur", 404, "Consultation d'ID : ".$id." inexistante");
            }
        } else if (!isset($_GET["idMedecin"]) &&
            !isset($_GET["idUsager"]) &&
            !isset($_GET["dateConsultation"])) {
            if (!empty($consultations = getConsultations($pdo, null, null, null))) {
                fournirReponse("Succes", 200, "Toutes les consultations récuperées", $consultations);
            } else {
                fournirReponse("Erreur", 404, "Aucune consultation récuperée");
            }
        } else {
            $idMedecin = isset($_GET["idMedecin"]) ? $_GET["idMedecin"] : null;
            $idUsager = isset($_GET["idUsager"]) ? $_GET["idUsager"] : null;
            $dateConsultation = isset($_GET["dateConsultation"]) ? $_GET["dateConsultation"] : null;
            if (!empty($consultationsFiltrees = getConsultations($pdo, $idMedecin, $idUsager, $dateConsultation))) {
                fournirReponse("Succes", 200, "Consultations filtrées récuperées", $consultationsFiltrees);
            } else {
                fournirReponse("Erreur", 404, "Aucune consultation trouvée correspondant à vos filtres");
            }
        }
        break;
    case "POST":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {
            $contenuFichier = file_get_contents('php://input');
            $arguments = json_decode($contenuFichier, true); 
            if (!empty($arguments["idMedecin"]) &&
                !empty($arguments["idUsager"]) &&
                !empty($arguments["date"]) &&
                !empty($arguments["heure"]) &&
                !empty($arguments["duree"])) {
                    if ($id = addConsultation($pdo, $arguments["idMedecin"], $arguments["idUsager"], $arguments["date"], $arguments["heure"], $arguments["duree"])) {
                        $consultation = getConsultationById($pdo, $id);
                        fournirReponse("Succes", 200, "Consultation créée", [$consultation]);
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue : la consultation n'a pas été créée");
                    }
            } else {
                fournirReponse("Erreur", 422, "Création consultation impossible, tous les champs ne sont pas renseignés");
            }
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }
        break;

    case "DELETE":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {
            if (!empty($_GET["id"])) {
                if (deleteConsultation($pdo, $_GET["id"])) {
                    fournirReponse("Succes", 200, "Consultation n°".$_GET["id"]." supprimée");
                } else {
                    fournirReponse("Erreur", 400, "Une erreur est survenue : aucune consultation n'a été supprimée");
                }
            } else {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
            }
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }
        break;

    case "PATCH":

        $jwt = get_bearer_token();

        if ($jwt && jetonValide($jwt)) {
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 
            if (!empty($_GET["id"]) && 
                (!empty($data["heure"]) ||
                !empty($data["duree"]))) {
                $id = $_GET["id"];
                $heure = $data["heure"] ?? null;
                $duree = $data["duree"] ?? null;
                if (editConsultation($pdo, $id, $heure, $duree)) {
                    $consultation = getConsultationById($pdo, $id);
                    fournirReponse("Succes", 200, "Consultation d'ID : ".$id." modifiée", $consultation);
                } else {
                    fournirReponse("Erreur", 400, "Une erreur est survenue : la consultation n'a pas été modifiée");
                }
            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
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
            $data = json_decode($postedData,true); 
            if (!empty($_GET["id"]) && 
                (!empty($data["heure"]) &&
                !empty($data["duree"]))) {
                $id = $_GET["id"];
                $heure = $data["heure"];
                $duree = $data["duree"];
                if (editConsultation($pdo, $id, $heure, $duree)) {
                    $consultation = getConsultationById($pdo, $id);
                    fournirReponse("Succes", 200, "Consultation d'ID : ".$id." intégralement modifiée", $consultation);
                } else {
                    fournirReponse("Erreur", 400, "Une erreur est survenue : aucune consultation n'a été modifiée");
                }
            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
            } else {
                fournirReponse("Erreur", 422, "Tous les paramètres doivent être saisis");
            }
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide");
        }
        break;
}

    