<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_medecins.php");
require("utilitairesAPI.php");

$pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_ressources", '350740', '$iutinfo');
$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method) {

    case "GET":

        $jwt = get_bearer_token();

        //on peut accéder aux médecins si l'on est connecté, peu importe notre rôle
        if ($jwt && jetonValide($jwt)) {

            //si un identifiant est renseigné, on cherche le médecin de cet identifiant
            if (isset($_GET["id"])) {

                $id = $_GET["id"];
                if (!empty($medecin = getMedecinById($pdo, $id))) {
                    fournirReponse("Succes", 200, "Medecin d'ID : ".$id." récuperé", $medecin);
                } else {
                    fournirReponse("Erreur", 404, "Medecin d'ID : ".$id." inexistant");
                }

            //autrement, si aucun filtre n'est renseigné, on fait une recherche globale 
            } else if (!isset($_GET["civilite"]) &&
                !isset($_GET["nom"]) &&
                !isset($_GET["prenom"])) {

                    if (!empty($medecin = getMedecins($pdo, null, null, null))) {
                        fournirReponse("Succes", 200, "Tous les médecins récuperés", $medecin);
                    } else {
                        fournirReponse("Erreur", 404, "Aucun médecin récuperé");
                    }

            //autrement, on fait une recherche selon les filtres
            } else {

                $civilite = isset($_GET["civilite"]) ? $_GET["civilite"] : null;
                $nom = isset($_GET["nom"]) ? $_GET["nom"] : null;
                $prenom = isset($_GET["prenom"]) ? $_GET["prenom"] : null;

                $medecinsFiltres = getMedecins($pdo, $civilite, $nom, $prenom);

                if (is_array($medecinsFiltres)) {
                    fournirReponse("Succes", 200, "Consultations filtrées récuperées", $medecinsFiltres);
                } else {
                    fournirReponse("Erreur", 400, "Aucun médecin récuperé");
                }
            }

        } else {

            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");

        }

        break;

    case "POST":

        $jwt = get_bearer_token();

        //on peut ajouter un médecin si on est connecté en tant qu'admin
        if ($jwt && jetonValide($jwt) && get_role($jwt) == "admin") {

            $contenuFichier = file_get_contents('php://input');
            $arguments = json_decode($contenuFichier, true); 

            //vérif si tous les arguments nécessaires sont renseignés
            if (!empty($arguments["civilite"]) &&
                !empty($arguments["nom"]) &&
                !empty($arguments["prenom"])) {

                    if ($id = addMedecin($pdo, $arguments["civilite"], $arguments["nom"], $arguments["prenom"])) {
                        $medecin = getMedecinById($pdo, $id);
                        fournirReponse("Succes", 200, "Médecin crée", $medecin);
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, le médecin n'a pas pu être ajouté");
                    }

            } else {
                fournirReponse("Erreur", 422, "Création du médecin impossible, tous les champs ne sont pas renseignés");
            }

        } else if (!jetonValide($jwt)) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }

        break;

    case "DELETE":

        $jwt = get_bearer_token();

        //on peut supprimer un médecin si on est connecté en tant qu'admin
        if ($jwt && jetonValide($jwt) && get_role($jwt) == "admin") {

            if (!empty($_GET["id"])) {
                if (deleteMedecin($pdo, $_GET["id"])) {
                    fournirReponse("Succes", 200, "Médecin n°".$_GET["id"]." supprimé");
                } else {
                    fournirReponse("Erreur", 400, "Aucun médecin supprimé");
                }
            } else {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
            }

        } else if (!jetonValide($jwt)) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }

        break;

    case "PATCH":

        $jwt = get_bearer_token();

        //on peut modifier un médecin si on est connecté en tant qu'admin
        if ($jwt && jetonValide($jwt) && get_role($jwt) == "admin") {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            if (!empty($_GET["id"]) && 
                (!empty($data["civilite"]) ||
                !empty($data["nom"]) || 
                !empty($data["prenom"]))) {

                $id = $_GET["id"];
                $civilite = $data["civilite"] ?? null;
                $nom = $data["nom"] ?? null;
                $prenom = $data["prenom"] ?? null;

                if (editMedecin($pdo, $id, $civilite, $nom, $prenom)) {
                    $medecin = getMedecinById($pdo, $id);
                    fournirReponse("Succes", 200, "Médecin d'ID : ".$id." modifié", $medecin);
                } else {
                    fournirReponse("Erreur", 400, "Médecin d'ID : ".$id." inchangé");
                }

            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
            } else {
                fournirReponse("Erreur", 422, "Au moins un paramètre doit être saisi");
            }

        } else if (!jetonValide($jwt)) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }

        break;
        
    case "PUT":

        $jwt = get_bearer_token();

        //on peut modifier un médecin si on est connecté en tant qu'admin
        if ($jwt && jetonValide($jwt) && get_role($jwt) == "admin") {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            if (!empty($_GET["id"]) && 
                (!empty($data["civilite"]) &&
                !empty($data["nom"]) &&
                !empty($data["prenom"]))) {

                $id = $_GET["id"];
                $civilite = $data["civilite"];
                $nom = $data["nom"];
                $prenom = $data["prenom"];
                
                if (editMedecin($pdo, $id, $civilite, $nom, $prenom)) {
                    $medecin = getMedecinById($pdo, $id);
                    fournirReponse("Succes", 200, "Médecin d'ID : ".$id." intégralement modifié", $medecin);
                } else {
                    fournirReponse("Erreur", 400, "Médecin d'ID : ".$id." inchangé");
                }

            } else if (empty($_GET["id"])) {
                fournirReponse("Erreur", 422, "Paramètre ID non spécifié");
            } else {
                fournirReponse("Erreur", 422, "Tous les paramètres doivent être saisis");
            }
            
        } else if (!jetonValide($jwt)) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;
}
?>

