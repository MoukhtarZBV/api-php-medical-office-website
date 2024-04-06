<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_medecins.php");
require("../db/connexionDB.php");
require("utilitairesAPI.php");

// Récupération du jeton
$jwt = get_bearer_token();

/* Jeton vérifié contenant 3 champs : 
 *      valide: détermine si le jeton est valide ou non
 *      role:   détermine le role de l'utilisateur
 *      id:     détermine l'ID de l'utilisateur selon son rôle (médecin ou usager)
*/
$jwtVerifie = verificationJeton($jwt);

$pdo = createConnection();
$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method) {
    
    /*******/
    /* GET */
    /*******/
    case "GET":
        // Accès aux médecins autorisé à tout le monde
        if ($jwtVerifie && $jwtVerifie["valide"]) {

            // Si un ID numérique a été renseigné
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"])) {

                $id = $_GET["id"];
                // Retour du médecin avec l'ID renseigné s'il existe
                if ($medecin = getMedecinById($pdo, $id)) {
                    fournirReponse("Succes", 200, "Medecin d'ID : ".$id." récuperé", $medecin);
                } else {
                    fournirReponse("Erreur", 404, "Medecin d'ID : ".$id." inexistant");
                }

            // Sinon, si l'ID n'est pas numérique, avertissement
            } else if (!empty($_GET["id"]) && !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "ID incorrect, veuillez saisir un entier");

            // Sinon, si aucun critere de recherche n'a été défini
            } else if (empty($_GET["civilite"]) &&
                empty($_GET["nom"]) &&
                empty($_GET["prenom"])) {

                    // Retour de tous les médecins
                    if (!empty($medecin = getMedecins($pdo, null, null, null))) {
                        fournirReponse("Succes", 200, "Tous les médecins récuperés", $medecin);
                    } else {
                        fournirReponse("Erreur", 404, "Aucun médecin trouvé");
                    }

            // Sinon, si au moins un critere de recherhe a été défini
            } else {
                // Récupération des critères de recherche renseignés
                $civilite = $_GET["civilite"] ?? null;
                $nom = $_GET["nom"] ?? null;
                $prenom = $_GET["prenom"] ?? null;

                // Retour des médecins filtrés selon les critères de recherche renseignés
                if ($medecinsFiltres = getMedecins($pdo, $civilite, $nom, $prenom)) {
                    fournirReponse("Succes", 200, "Médecins filtrés récuperés", $medecinsFiltres);
                } else {
                    fournirReponse("Erreur", 404, "Aucun médecin correspondant à vos filtres");
                }
            }

        // Jeton invalide
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        }
        break;

    /********/
    /* POST */
    /********/
    case "POST":
        // Création d'un médecin autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            $contenuFichier = file_get_contents('php://input');
            $arguments = json_decode($contenuFichier, true); 

            // Si tous les champs ont été renseignés
            if (!empty($arguments["civilite"]) &&
                !empty($arguments["nom"]) &&
                !empty($arguments["prenom"])) {

                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($arguments["civilite"], $arguments["nom"], $arguments["prenom"]);

                // Retour du médecin ajouté si l'ajout est un succès
                if ($id = addMedecin($pdo, $arguments["civilite"], $arguments["nom"], $arguments["prenom"])) {
                    $medecin = getMedecinById($pdo, $id);
                    fournirReponse("Succes", 201, "Médecin crée", $medecin);
                } else {
                    fournirReponse("Erreur", 400, "Une erreur est survenue, le médecin n'a pas pu être ajouté");
                }

            // Sinon, si au moins un champ n'a pas été renseigné, affichage d'une erreur
            } else {
                fournirReponse("Erreur", 422, "Création du médecin impossible, tous les champs ne sont pas renseignés");
            }

        // Jeton invalide
        } else if (!$jwtVerifie || !$jwtVerifie["valide"]) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        // L'utilisateur ne possède pas l'autorisation (le role) requise
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;

    /**********/
    /* DELETE */
    /**********/
    case "DELETE":
        // Suppression d'un médecin autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            // Si un ID numérique a été renseigné
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"])) {

                // Vérification du succès ou de l'échec de la suppression
                if (deleteMedecin($pdo, $_GET["id"])) {
                    fournirReponse("Succes", 200, "Médecin d'ID : ".$_GET["id"]." supprimé");
                } else {
                    fournirReponse("Erreur", 404, "Médecin d'ID : ".$_GET["id"]." inexistant");
                }

            // Sinon, affichage d'une erreur
            } else {
                fournirReponse("Erreur", 403, "L'identifiant du médecin n'est pas renseigné ou n'est pas un entier");
            }

        // Jeton invalide
        } else if (!$jwtVerifie || !$jwtVerifie["valide"]) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        // L'utilisateur ne possède pas l'autorisation (le role) requise
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;

    /*********/
    /* PATCH */
    /*********/
    case "PATCH":
        // Modification d'un médecin autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            // Si l'ID n'est pas renseigné ou n'est pas un entier
            if (empty($_GET["id"]) || !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "L'identifiant du médecin n'est pas renseigné ou n'est pas un entier");
        
            // Sinon, si au moins un champ a été renseigné, on procède à la modification
            } else if ((!empty($data["civilite"]) ||
                !empty($data["nom"]) || 
                !empty($data["prenom"]))) {

                // Vérification de l'existence du médecin
                if (!getMedecinById($pdo, $_GET["id"])) {
                    fournirReponse("Erreur", 404, "Médecin d'ID : ". $_GET["id"] ." inexistant");
                    exit();
                }

                $id = $_GET["id"];
                $civilite = $data["civilite"] ?? null;
                $nom = $data["nom"] ?? null;
                $prenom = $data["prenom"] ?? null;
                
                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($civilite, $nom, $prenom);

                // Vérification du succès ou de l'échec de la modification
                if (editMedecin($pdo, $id, $civilite, $nom, $prenom) > 0) {
                    $medecin = getMedecinById($pdo, $id);
                    fournirReponse("Succes", 200, "Médecin d'ID : ".$id." modifié", $medecin);
                } else {
                    fournirReponse("Erreur", 409, "Médecin d'ID : ".$id." inchangé car il contient déjâ ces informations");
                }

            // Sinon, si aucun champ n'est renseigné
            } else {
                fournirReponse("Erreur", 422, "Au moins un paramètre doit être saisi");
            }

        // Jeton invalide
        } else if (!$jwtVerifie || !$jwtVerifie["valide"]) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        // L'utilisateur ne possède pas l'autorisation (le role) requise
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;
       
    /*******/
    /* PUT */
    /*******/
    case "PUT":
        // Modification ou création d'un médecin autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            // Si un ID entier et tous les champs ont été renseignés, on procède à la modification ou la création
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"]) &&
                (!empty($data["civilite"]) &&
                !empty($data["nom"]) &&
                !empty($data["prenom"]))) {

                $id = $_GET["id"];
                $civilite = $data["civilite"];
                $nom = $data["nom"];
                $prenom = $data["prenom"];
                
                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($civilite, $nom, $prenom);

                // Si le médecin existe déjâ, on le modifie
                if (getMedecinById($pdo, $id)) {

                    // Vérification du succès ou de l'échec de la modification
                    if (editMedecin($pdo, $id, $civilite, $nom, $prenom)) {
                        $medecin = getMedecinById($pdo, $id);
                        fournirReponse("Succes", 200, "Médecin d'ID : ".$id." intégralement modifié", $medecin);
                    } else {
                        fournirReponse("Erreur", 409, "Médecin d'ID : ".$id." inchangé car il contient déjâ ces informations");
                    }

                // Sinon, on le crée
                } else {
                    // Retour du médecin ajouté si l'ajout est un succès
                    if ($id = addMedecin($pdo, $arguments["civilite"], $arguments["nom"], $arguments["prenom"])) {
                        $medecin = getMedecinById($pdo, $id);
                        fournirReponse("Succes", 201, "Médecin crée", $medecin);
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, le médecin n'a pas pu être ajouté");
                    }
                }

            // Sinon, si l'ID n'est pas renseigné ou n'est pas un entier
            } else if (empty($_GET["id"]) || !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "L'identifiant du médecin n'est pas renseigné ou n'est pas un entier");
            // Sinon, si au moins un champ n'est pas renseigné
            } else {
                fournirReponse("Erreur", 422, "Tous les paramètres doivent être saisis");
            }
            
        // Jeton invalide
        } else if (!$jwtVerifie || !$jwtVerifie["valide"]) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        // L'utilisateur ne possède pas l'autorisation (le role) requise
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;
    
    // Méthodes non autorisées
    default:
        fournirReponse("Erreur", 405, "Méthode non autorisée");
        break;
}


/**
 * Vérifie que tous les champs non-null fournis sont valide avec des vérifications
 * propres à chaque champ, et retourne une erreur et arrête le script le cas échéant
 *
 * @param mixed $civilite       Civilité du médecin
 * @param mixed $nom            Nom du médecin
 * @param mixed $prenom         Prénom du médecin
 * @return void
 */
function verifierChamps(mixed $civilite, mixed $nom, mixed $prenom) {
    if (isset($civilite) && (tailleChampPlusGrandeQue($civilite, 4) || !contientLettresUniquement($civilite))) {
        fournirReponse("Erreur", 422, "La civilité du médecin est invalide");
        exit();
    } else if (isset($nom) && (tailleChampPlusGrandeQue($nom, 50) || !contientLettresUniquement($nom))) {
        fournirReponse("Erreur", 422, "Le nom du médecin est invalide");
        exit();
    } else if (isset($prenom) && (tailleChampPlusGrandeQue($prenom, 50) || !contientLettresUniquement($prenom))) {
        fournirReponse("Erreur", 422, "Le prénom du médecin est invalide");
        exit();
    }
}