<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_consultations.php");
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
        // Accès aux consultations autorisé aux admins, aux médecins et aux usagers concernés
        if ($jwtVerifie && $jwtVerifie["valide"]) {

            // Si un ID numérique a été renseigné
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"])) {

                $id = $_GET["id"];
                // Si la consultation avec l'ID renseigné existe
                if ($consultation = getConsultationById($pdo, $id)) {
                    // S'il s'agit d'un usager qui n'est pas concerné par la consultation, affichage d'une erreur
                    if (isUsager($jwtVerifie) && !isRightUsager($consultation["idUsager"], $jwtVerifie)) {
                        fournirReponse("Erreur", 401, "Vous ne pouvez pas récupérer une consultation qui ne vous concerne pas");
                    // Sinon, affichage de la consultation
                    } else {
                        fournirReponse("Succes", 200, "Consultation d'ID : ".$id." récuperée", $consultation);
                    }

                // Sinon, affichage d'une erreur
                } else {
                    fournirReponse("Erreur", 404, "Consultation d'ID : ".$id." inexistante");
                }

            // Sinon, si l'ID n'est pas numérique, avertissement
            } else if (!empty($_GET["id"]) && !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "ID incorrect, veuillez saisir un entier");

            // Sinon, si aucun champ n'a été défini
            } else if (empty($_GET["idMedecin"]) &&
                empty($_GET["idUsager"]) &&
                empty($_GET["date"])) {
                    // Si il s'agit d'un usager, on récupère uniquement ses consultations
                    $idUsager = isUsager($jwtVerifie) ? $jwtVerifie["id"] : null;

                    // Récupération de toutes les consultations
                    if ($consultations = getConsultations($pdo, null, $idUsager, null)) {
                        fournirReponse("Succes", 200, "Toutes les consultations récuperées", $consultations);
                    } else {
                        fournirReponse("Erreur", 404, "Aucune consultation trouvée");
                    }

            // Sinon, si au moins un champ a été défini
            } else {

                $idMedecin = $_GET["idMedecin"] ?? null;
                $dateConsultation = $_GET["date"] ?? null;
                
                // Si il s'agit d'un usager, on récupère uniquement ses consultations
                if (isUsager($jwtVerifie)) {
                    $idUsager = $jwtVerifie["id"];
                } else {
                    $idUsager = $_GET["idUsager"] ?? null;
                }

                // Vérification des champs
                verifierChamps($idMedecin, $idUsager, $dateConsultation, null, null);

                // Récupération des consultations filtrées selon les champs définis
                if ($consultationsFiltrees = getConsultations($pdo, $idMedecin, $idUsager, $dateConsultation)) {
                    fournirReponse("Succes", 200, "Consultations filtrées récuperées", $consultationsFiltrees);
                } else {
                    fournirReponse("Erreur", 404, "Aucune consultation correspondant à vos filtres");
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
        $contenuFichier = file_get_contents('php://input');
        $arguments = json_decode($contenuFichier, true); 

        // Création d'une consultation autorisée aux admins et au médecin concerné par la consultation
        if ($jwtVerifie && $jwtVerifie["valide"] &&
            (isAdmin($jwtVerifie) || isRightMedecin($arguments["id_medecin"], $jwtVerifie))) {

            // Si aucun des champs n'est vide
            if (!empty($arguments["id_medecin"]) &&
                !empty($arguments["id_usager"]) &&
                !empty($arguments["date_consult"]) &&
                !empty($arguments["heure_consult"]) &&
                !empty($arguments["duree_consult"])) {

                    // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                    verifierChamps($arguments["id_medecin"], $arguments["id_usager"], $arguments["date_consult"], $arguments["heure_consult"], $arguments["duree_consult"]);

                    // Récupération de l'ID ou du code d'erreur de la consultation ajoutée
                    $id = addConsultation($pdo, $arguments["id_medecin"], $arguments["id_usager"], $arguments["date_consult"], $arguments["heure_consult"], $arguments["duree_consult"]);

                    // Si l'ID est positif, alors la consultation a bien été ajoutée
                    if ($id > 0) {
                        $consultation = getConsultationById($pdo, $id);
                        fournirReponse("Succes", 201, "Consultation créée", [$consultation]);
                    // Sinon, si le code d'erreur vaut -1, la consultation chevauche avec une autre consultation du médecin
                    } else if ($id == -1) {
                        fournirReponse("Erreur", 422, "La consultation chevauche sur un autre créneau pour ce médecin");
                    // Sinon, si le code d'erreur vaut -2, le médecin et/ou l'usager attribué(s) à la consultation n'existe(nt) pas
                    } else if ($id == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin ou usager portant cet ID");
                    // Sinon, une autre erreur a eu lieu
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, la consultation n'a pas été créée");
                    }

            } else {
                fournirReponse("Erreur", 422, "Création consultation impossible, tous les champs ne sont pas renseignés");
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
        verifierID();
        $idMedecin = verifierExistenceConsultation($pdo)["idMedecin"];

        // Suppression d'une consultation autorisée aux admins et au médecin concerné par la consultation
        if ($jwtVerifie && $jwtVerifie["valide"] &&
            (isAdmin($jwtVerifie) || isRightMedecin($idMedecin, $jwtVerifie))) {

            // Vérification du succès ou de l'échec de la suppression
            if (deleteConsultation($pdo, $_GET["id"])) {
                fournirReponse("Succes", 200, "Consultation d'ID : ".$_GET["id"]." supprimée");
            } else {
                fournirReponse("Erreur", 400, "Une erreur est survenue, aucune consultation n'a été supprimée");
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
        verifierID();
        $idMedecin = verifierExistenceConsultation($pdo)["idMedecin"];

        // Modification d'une consultation autorisée aux admins et au médecin concerné
        if ($jwtVerifie && $jwtVerifie["valide"] &&
            (isAdmin($jwtVerifie) || isRightMedecin($idMedecin, $jwtVerifie))) {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            // Si au moins un champ est défini
            if ((!empty($data["id_usager"]) ||
                !empty($data["id_medecin"]) ||
                !empty($data["date_consult"]) ||
                !empty($data["heure_consult"]) ||
                !empty($data["duree_consult"]))) {

                // Vérification de l'existence de la consultation
                if (!getConsultationById($pdo, $_GET["id"])) {
                    fournirReponse("Erreur", 404, "Consultation d'ID : ".$_GET["id"]." inexistante");
                    exit();
                }

                $id = $_GET["id"];
                $idUsager = $date["id_usager"] ?? null;
                $idMedecin = $data["id_medecin"] ?? null;
                $date = $data["date_consult"] ?? null;
                $heure = $data["heure_consult"] ?? null;
                $duree = $data["duree_consult"] ?? null;

                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($idMedecin, $idUsager, $date, $heure, $duree);
                
                // Récupération du résultat de la modification
                $modification = editConsultation($pdo, $id, $idUsager, $idMedecin, $date, $heure, $duree);

                // Si une ligne a été affectée, la consultation a bien été modifiée
                if ($modification > 0) {
                    $consultation = getConsultationById($pdo, $id);
                    fournirReponse("Succes", 200, "Consultation d'ID : ".$id." modifiée", $consultation);
                // Sinon, si le code de retour vaut -1, la consultation chevauche avec une autre
                } else if ($modification == -1) {
                    fournirReponse("Erreur", 422, "La consultation chevauche sur un autre créneau pour ce médecin");
                // Sinon, si le code de retour vaut -1, le médecin et/ou l'usager attribué(s) à la consultation n'existe(nt) pas
                } else if ($modification == -2) {
                    fournirReponse("Erreur", 422, "Aucun médecin ou usager portant cet ID");
                // Sinon, aucune modification n'a eu lieu
                } else {
                    fournirReponse("Erreur", 409, "Consultation d'ID : ".$id." inchangée car elle contient déjâ ces informations");
                }
            
            // Si aucun champ n'a été défini
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
        verifierID();

        // Modification ou création d'une consultation autorisée aux admins et au médecin concerné
        if ($jwtVerifie && $jwtVerifie["valide"] &&
            (isAdmin($jwtVerifie) || isRightMedecin($idMedecin, $jwtVerifie))) {

            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData,true); 

            // Si tous les champs ont été définis
            if ((!empty($data["id_usager"]) ||
                !empty($data["id_medecin"]) ||
                !empty($data["date_consult"]) ||
                !empty($data["heure_consult"]) ||
                !empty($data["duree_consult"]))) {

                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($arguments["id_medecin"], $arguments["id_usager"], $arguments["date_consult"], $arguments["heure_consult"], $arguments["duree_consult"]);

                $id = $_GET["id"];

                // Si la consultation existait déjâ, on procède à la mise à jour
                if (getUsagerById($pdo, $id)) {
                    // Récupération du résultat de la modification
                    $modification = editConsultation($pdo, $id, $data["id_usager"], $data["id_medecin"], $data["date_consult"], $data["heure_consult"], $data["duree_consult"]);

                    // Si une ligne a été affectée, la consultation a bien été modifiée
                    if ($modification > 0) {
                        $consultation = getConsultationById($pdo, $id);
                        fournirReponse("Succes", 200, "Consultation d'ID : ".$id." intégralement modifiée", $consultation);
                    // Sinon, si le code de retour vaut -1, la consultation chevauche avec une autre
                    } else if ($modification == -1) {
                        fournirReponse("Erreur", 422, "La consultation chevauche sur un autre créneau pour ce médecin");
                    // Sinon, si le code de retour vaut -1, le médecin et/ou l'usager attribué(s) à la consultation n'existe(nt) pas
                    } else if ($modification == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin ou usager portant cet ID");
                    // Sinon, les colonnes changées possédaient la même valeur avant la mise à jour, donc rien n'a changé
                    } else {
                        fournirReponse("Erreur", 409, "Consultation d'ID : ".$id." inchangée car elle contient déjâ ces informations");
                    }

                // Sinon, on la crée
                } else {
                    // Récupération de l'ID de la consultation ajoutée
                    $id = addConsultation($pdo, $arguments["id_medecin"], $arguments["id_usager"], $arguments["date_consult"], $arguments["heure_consult"], $arguments["duree_consult"]);

                    // Si l'ID est positif, alors la consultation a bien été ajoutée
                    if ($id > 0) {
                        $consultation = getConsultationById($pdo, $id);
                        fournirReponse("Succes", 201, "Consultation créée", [$consultation]);
                    // Sinon, si l'ID vaut -1, la consultation chevauche avec une autre consultation du médecin
                    } else if ($id == -1) {
                        fournirReponse("Erreur", 422, "La consultation chevauche sur un autre créneau pour ce médecin");
                    // Sinon, si l'ID vaut -2, le médecin et/ou l'usager attribué(s) à la consultation n'existe(nt) pas
                    } else if ($id == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin ou usager portant cet ID");
                    // Sinon, une autre erreur a eu lieu
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, la consultation n'a pas été créée");
                    }
                }

            // Si au moins un champ n'a pas été défini
            } else {
                fournirReponse("Erreur", 422, "Un ou plusieurs paramètre(s) manquant(s)");
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
 * Vérifie si l'ID est saisi et si il représente un entier
 * affiche un message de retour et interrompt le script si ce n'est pas le cas
 *
 * @return void
 */
function verifierID() {
    if (empty($_GET["id"]) || !ctype_digit($_GET["id"])) {
        fournirReponse("Erreur", 403, "L'identifiant de la consultation n'est pas renseigné ou n'est pas un entier");
        exit();
    } 
}

/**
 * Vérifie si la consultation avec l'ID passé en URL existe
 * affiche un message de retour et interrompt le script si ce n'est pas le cas
 * et renvoie la consultation si ca l'est
 * 
 * @param PDO $pdo  PDO pour établir la connexion à la base de données
 * @return array    La consultation, si elle existe
 */
function verifierExistenceConsultation(PDO $pdo) : array {
    if (!$consultation = getConsultationById($pdo, $_GET["id"])) {
        fournirReponse("Erreur", 404, "Consultation d'ID : " .$_GET["id"]. " inexistante");
        exit();
    }
    return $consultation;
}

/**
 * Vérifie que tous les champs non-null fournis sont valide avec des vérifications
 * propres à chaque champ, et retourne une erreur et arrête le script le cas échéant
 *
 * @param mixed $idMedecin  ID du médecin concerné par la consultation
 * @param mixed $idUsager   ID de l'usager concerné par la consultation
 * @param mixed $date       Date de la consultation
 * @param mixed $heure      Heure de la consultation
 * @param mixed $duree      Durée de la consultation
 * @return void
 */
function verifierChamps(mixed $idMedecin, mixed $idUsager, mixed $date, mixed $heure, mixed $duree) {
    if (isset($idMedecin) && !nombrePositif($idMedecin)) {
        fournirReponse("Erreur", 422, "L'ID du médecin est invalide");
        exit();
    } else if (isset($idUsager) && !nombrePositif($idUsager)) {
        fournirReponse("Erreur", 422, "L'ID de l'usager est invalide");
        exit();
    } else if (isset($date) && !formatDateCorrect($date, 'd/m/y')) {
        fournirReponse("Erreur", 422, "La date de la consultation est invalide");
        exit();
    } else if (isset($heure) && !formatHeureCorrect($heure)) {
        fournirReponse("Erreur", 422, "L'heure de début de la consultation est invalide (Format à respecter : HH:MM ou HH:MM:SS)");
        exit();
    } else if (isset($duree) && !nombrePositif($duree)) {
        fournirReponse("Erreur", 422, "La durée de la consultation est invalide");
        exit();
    }
}
    