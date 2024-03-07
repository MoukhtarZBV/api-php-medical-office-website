<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAOconsultations.php");
require("../authapi/jwt_utils.php");

$pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_ressources", '350740', '$iutinfo');
$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method) {
    case "GET":
        if (!isset($_GET["idMedecin"]) &&
            !isset($_GET["idUsager"]) &&
            !isset($_GET["dateConsultation"])) {
            if (!empty($allConsultations = getConsultations($pdo, null, null, null))) {
                fournirReponse("Succes", 200, "Toutes les consultations récuperées", $allConsultations);
            }
        } else {
            $idMedecin = isset($_GET["idMedecin"]) ? $_GET["idMedecin"] : null;
            $idUsager = isset($_GET["idUsager"]) ? $_GET["idUsager"] : null;
            $dateConsultation = isset($_GET["dateConsultation"]) ? $_GET["dateConsultation"] : null;
            if (!empty($consultationsFiltrees = getConsultations($pdo, $idMedecin, $idUsager, $dateConsultation))) {
                fournirReponse("Succes", 200, "Consultations filtrées récuperées", $consultationsFiltrees);
            }
        }
        break;
    case "POST":
        $jwt = get_bearer_token();
        if ($jwt && is_jwt_valid($jwt, 'secret')) {
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
                        fournirReponse("Erreur", 400, "Erreur lors de la création de la consultation");
                    }
            } else {
                fournirReponse("Erreur", 400, "Création consultation impossible, tous les champs ne sont pas renseignés");
            }
        } else {
            fournirReponse("Erreur", 400, "Jeton invalide");
        }
        break;
    case "DELETE":
        $jwt = get_bearer_token();
        if ($jwt && is_jwt_valid($jwt, 'secret')) {
            if (!empty($_GET["id"])) {
                if (deleteConsultation($pdo, $_GET["id"])) {
                    fournirReponse("Succes", 200, "Consultation n°".$_GET["id"]." supprimée");
                } else {
                    fournirReponse("Erreur", 400, "Aucune consultation supprimée");
                }
            }
        } else {
            fournirReponse("Erreur", 400, "Jeton invalide");
        }
        break;
    case "PATCH":
        $jwt = get_bearer_token();
        if ($jwt && is_jwt_valid($jwt, 'secret')) {
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 
            if (!empty($_GET["id"]) && 
                (!empty($data["heure"]) ||
                !empty($data["duree"]))) {
                $id = $_GET["id"];
                $heure = isset($data["heure"]) ? $data["heure"] : null;
                $duree = isset($data["duree"]) ? $data["duree"] : null;
                if (editConsultation($pdo, $id, $heure, $duree)) {
                    $consultation = getConsultationById($pdo, $id);
                    fournirReponse("Succes", 200, "Consultation d'ID : ".$id." modifiée", $consultation);
                } else {
                    fournirReponse("Erreur", 400, "Consultation d'ID : ".$id." inchangée");
                }
            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 400, "Paramètre ID non spécifié");
            } else {
                fournirReponse("Erreur", 400, "Au moins un paramètre doit être saisi");
            }
        } else {
            fournirReponse("Erreur", 400, "Jeton invalide");
        }
        break;
    case "PUT":
        $jwt = get_bearer_token();
        if ($jwt && is_jwt_valid($jwt, 'secret')) {
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 
            if (!empty($_GET["id"]) && 
                (!empty($data["heure"]) &&
                !empty($data["duree"]))) {
                $id = $_GET["id"];
                $heure = isset($data["heure"]) ? $data["heure"] : null;
                $duree = isset($data["duree"]) ? $data["duree"] : null;
                if (editConsultation($pdo, $id, $heure, $duree)) {
                    $consultation = getConsultationById($pdo, $id);
                    fournirReponse("Succes", 200, "Consultation d'ID : ".$id." intégralement modifiée", $consultation);
                } else {
                    fournirReponse("Erreur", 400, "Consultation d'ID : ".$id." inchangée");
                }
            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 400, "Paramètre ID non spécifié");
            } else {
                fournirReponse("Erreur", 400, "Tous les paramètres doivent être saisis");
            }
        } else {
            fournirReponse("Erreur", 400, "Jeton invalide");
        }
        break;
}

function fournirReponse(string $statut, string $statutCode, string $statutMessage, array | null $donnees = null) : void {
    http_response_code($statutCode);
    header("Content-Type:application/json; charset=utf-8");
    $reponse['statut'] = $statut;
    $reponse['statutCode'] = $statutCode;
    $reponse['statutMessage'] = $statutMessage;
    $reponse['donnees'] = $donnees;
    $reponseJson = json_encode($reponse);
    if ($reponseJson === false) {
        die('json encode ERROR : ' . json_last_error_msg());
    }
    echo $reponseJson;
}
    