<?php
header("Access-Control-Allow-Origin: *");
require("../db/DAO_usagers.php");
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
        // Accès aux usagers autorisé aux admins et aux médecins
        if ($jwtVerifie && $jwtVerifie["valide"] && (isAdmin($jwtVerifie) || isMedecin($jwtVerifie))) {
            
            // Si un ID numérique a été renseigné
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"])) {

                $id = $_GET["id"];
                // Retour du médecin avec l'ID renseigné s'il existe
                if ($usager = getUsagerById($pdo, $id)) {    
                    fournirReponse("Succes", 200, "Usager d'ID ".$id." récuperé", $usager);
                } else {
                    fournirReponse("Erreur", 404, "Usager d'ID ".$id." inexistant");
                }

            // Sinon, si l'ID n'est pas numérique, avertissement
            } else if (!empty($_GET["id"]) && !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "ID incorrect, veuillez saisir un entier");
                
            // Sinon, si aucun critere de recherche n'a été défini
            } else if (empty($_GET["civilite"]) && empty($_GET["nom"]) && 
                empty($_GET["prenom"]) && empty($_GET["numeroSecuriteSociale"])) {

                // Retour de tous les usagers
                if ($usagers = getUsagers($pdo, null, null, null, null)) {
                    fournirReponse("Succes", 200, "Tous les usagers récuperés", $usagers);  
                } else {
                    fournirReponse("Erreur", 404, "Aucun usager trouvé");
                }

            // Sinon, si au moins un citère a été défini
            } else {

                // Récupération des critères de recherche 
                $civilite = $_GET["civilite"] ?? null;
                $nom = $_GET["nom"] ?? null;
                $prenom = $_GET["prenom"] ?? null;
                $numSS = $_GET["numeroSecuriteSociale"] ?? null;
                
                // Vérification des champs
                verifierChamps($civilite, $nom, $prenom, null, null, null, null, $numSS, null, null, null);

                // Retour des usagers filtrés selon les critères de recherche renseignés
                if ($usagersFiltres = getUsagers($pdo, $civilite, $nom, $prenom, $numSS)) {
                    fournirReponse("Succes", 200, "Usagers filtrés récuperés", $usagersFiltres);
                } else {
                    fournirReponse("Erreur", 404, "Aucun usager correspondant à vos filtres");
                }
            }

        // Jeton invalide
        } else if (!$jwtVerifie || !$jwtVerifie["valide"]) {
            fournirReponse("Erreur", 401, "Jeton invalide, votre session a peut être expiré");
        // L'utilisateur ne possède pas l'autorisation (le role) requise
        } else {
            fournirReponse("Erreur", 401, "Jeton invalide, vous n'avez pas l'autorisation pour cette action");
        }
        break;

    /********/
    /* POST */
    /********/
    case "POST":
        // Création d'un usager autorisée aux admins et aux médecins
        if ($jwtVerifie && $jwtVerifie["valide"] && (isAdmin($jwtVerifie) || isMedecin($jwtVerifie))) {

            $contenuFichier = file_get_contents('php://input');
            $arguments = json_decode($contenuFichier, true); 

            // Si tous les champs ont été renseignés
            if (!empty($arguments["civilite"]) && !empty($arguments["sexe"]) &&
                !empty($arguments["nom"]) && !empty($arguments["prenom"]) && 
                !empty($arguments["adresse"]) && !empty($arguments["ville"]) && 
                !empty($arguments["code_postal"]) && !empty($arguments["num_secu"]) && 
                !empty($arguments["date_nais"]) && !empty($arguments["lieu_nais"])) {

                    // Récupération d'un possible champ facultatif
                    $medRef = $arguments["id_medecin"] ?? null;

                    // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                    verifierChamps($arguments["civilite"], $arguments["nom"], $arguments["prenom"], $arguments["sexe"], $arguments["adresse"], $arguments["ville"],
                                    $arguments["code_postal"], $arguments["num_secu"], $arguments["date_nais"], $arguments["lieu_nais"], $medRef);
                    
                    // Récupération de l'ID ou du code d'erreur de l'usager ajouté
                    $id = addUsager($pdo, $arguments["civilite"], $arguments["sexe"], $arguments["nom"], $arguments["prenom"], 
                                    $arguments["adresse"], $arguments["ville"], $arguments["code_postal"], $arguments["num_secu"],
                                    $arguments["date_nais"], $arguments["lieu_nais"], $medRef);

                    // Si l'ID est positif, alors l'usager a bien été ajouté
                    if ($id > 0) {
                        $usager = getUsagerById($pdo, $id);
                        fournirReponse("Succes", 201, "Usager crée", $usager);
                    // Sinon, si le code d'erreur vaut -1, un usager avec le numéro de sécurité sociale renseigné existe déjâ
                    } else if ($id == -1) {
                        fournirReponse("Erreur", 409, "Un usager portant ce numéro de sécurité sociale existe déjâ");
                    // Sinon, si le code d'erreur vaut -2, aucun médecin avec l'ID renseigné n'existe
                    } else if ($id == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin portant l'ID : " . $medRef);
                    // Sinon, une autre erreur a eu lieu
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, l'usager n'a pas été crée");
                    }

            // Sinon, si au moins un champ n'a pas été renseigné, affichage d'une erreur
            } else {
                fournirReponse("Erreur", 422, "Création d'usager impossible, tous les champs ne sont pas renseignés");
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
        // Suppression d'un usager autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            // Si un ID numérique a été renseigné
            if (!empty($_GET["id"]) && ctype_digit($_GET["id"])) {

                // Vérification du succès ou de l'échec de la suppression
                if (deleteUsager($pdo, $_GET["id"])) {
                    fournirReponse("Succes", 200, "Usager d'ID : ".$_GET["id"]." supprimé");
                } else {
                    fournirReponse("Erreur", 404, "Usager d'ID : ".$_GET["id"]." inexistant");
                }

            // Sinon, affichage d'une erreur
            } else {
                fournirReponse("Erreur", 403, "L'identifiant de l'usager n'est pas renseigné ou n'est pas un entier");
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
        // Modification d'un usager autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            $postedData = file_get_contents('php://input');
            $arguments = json_decode($postedData,true); 

            // Si l'ID n'est pas renseigné ou n'est pas un entier
            if (empty($_GET["id"]) || !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "L'identifiant de l'usager n'est pas renseigné ou n'est pas un entier");
        
            // Sinon, si au moins un champ a été renseigné, on procède à la modification
            } else if (!empty($arguments["civilite"]) || !empty($arguments["nom"]) ||
                !empty($arguments["prenom"]) || !empty($arguments["adresse"]) ||
                !empty($arguments["ville"]) || !empty($arguments["code_postal"]) ||
                !empty($arguments["num_secu"]) || !empty($arguments["date_nais"]) || 
                !empty($arguments["lieu_nais"]) || !empty($arguments["sexe"]) ||
                !empty($arguments["id_medecin"])) {

                // Vérification de l'existence de l'usager
                if (!getUsagerById($pdo, $_GET["id"])) {
                    fournirReponse("Erreur", 404, "Usager d'ID : ".$_GET["id"]." inexistant");
                    exit();
                }

                $id = $_GET["id"];

                $civ = $arguments["civilite"] ?? null;
                $sexe = $arguments["sexe"] ?? null;
                $nom = $arguments["nom"] ?? null;
                $prenom = $arguments["prenom"] ?? null;
                $adresse = $arguments["adresse"] ?? null;
                $ville = $arguments["ville"] ?? null;
                $codePostal = $arguments["code_postal"] ?? null;
                $numeroSecuriteSociale = $arguments["num_secu"] ?? null;
                $dateNaissance = $arguments["date_nais"] ?? null;
                $lieuNaissance = $arguments["lieu_nais"] ?? null;
                $medRef = $arguments["id_medecin"] ?? null;

                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($civ, $nom, $prenom, $sexe, $adresse, $ville, $codePostal, $numeroSecuriteSociale,
                                $dateNaissance, $lieuNaissance, $medRef);

                // Récupération du résultat de la modification
                $modification = editUsager($pdo, $id, $civ, $sexe, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale,
                                        $dateNaissance, $lieuNaissance, $medRef);

                // Si une ligne a été affectée, l'usager a bien été modifié
                if ($modification > 0) {
                    $usager = getUsagerById($pdo, $_GET["id"]);
                    fournirReponse("Succes", 200, "Usager d'ID : ".$id." modifié", $usager);
                // Sinon, si le code d'erreur vaut -1, un usager avec le numéro de sécurité sociale renseigné existe déjâ
                } else if ($id == -1) {
                    fournirReponse("Erreur", 409, "Un usager portant ce numéro de sécurité sociale existe déjâ");
                // Sinon, si le code d'erreur vaut -2, aucun médecin avec l'ID renseigné n'existe
                } else if ($id == -2) {
                    fournirReponse("Erreur", 422, "Aucun médecin portant l'ID : " . $medRef);
                // Sinon, aucune modification n'a eu lieu
                } else {
                    fournirReponse("Erreur", 409, "Usager d'ID : ".$id." inchangé car il contient déjâ ces informations");
                }

            // Sinon, si aucun champ n'a été saisi
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
        // Modification (ou création s'il n'existe pas) d'un usager autorisée aux admins uniquement
        if ($jwtVerifie && $jwtVerifie["valide"] && isAdmin($jwtVerifie)) {

            $postedData = file_get_contents('php://input');
            $arguments = json_decode($postedData,true); 

            // Si l'ID n'est pas renseigné ou n'est pas un entier
            if (empty($_GET["id"]) || !ctype_digit($_GET["id"])) {
                fournirReponse("Erreur", 403, "L'identifiant de l'usager n'est pas renseigné ou n'est pas un entier");

            // Sinon, si tous les champs ont été renseignés, on procède à la modification
            } else if (!(empty($arguments["civilite"]) || empty($arguments["nom"]) ||
                empty($arguments["prenom"]) || empty($arguments["adresse"]) ||
                empty($arguments["ville"]) || empty($arguments["code_postal"]) ||
                empty($arguments["num_secu"]) || empty($arguments["date_nais"]) || 
                empty($arguments["lieu_nais"]) || empty($arguments["sexe"]) || 
                empty($arguments["id_medecin"])) && !empty($_GET["id"])) {

                // Vérifie tous les champs et retourne une erreur si un des champs est invalide
                verifierChamps($arguments["civilite"], $arguments["nom"], $arguments["prenom"], $arguments["sexe"], $arguments["adresse"], $arguments["ville"],
                                $arguments["code_postal"], $arguments["num_secu"], $arguments["date_nais"], $arguments["lieu_nais"], $arguments["id_medecin"]);

                // Si l'usager existe déjâ, on le modifie
                if (getUsagerById($pdo, $id)) {
                    // Récupération du résultat de la modification
                    $modification = editUsager($pdo, $arguments["id"], $arguments["civilite"], $arguments["sexe"], $arguments["nom"], $arguments["prenom"], 
                                            $arguments["adresse"], $arguments["ville"], $arguments["code_postal"], $arguments["num_secu"],
                                            $arguments["date_nais"], $arguments["lieu_nais"], $arguments["id_medecin"]);

                    // Si une ligne a été affectée, l'usager a bien été modifié
                    if ($modification > 0) {
                        $usager = getUsagerById($pdo, $_GET["id"]);
                        fournirReponse("Succes", 200, "Usager d'ID : ".$id." modifié", $usager);
                    // Sinon, si le code d'erreur vaut -1, un usager avec le numéro de sécurité sociale renseigné existe déjâ
                    } else if ($id == -1) {
                        fournirReponse("Erreur", 422, "Un usager portant ce numéro de sécurité sociale existe déjâ");
                    // Sinon, si le code d'erreur vaut -2, aucun médecin avec l'ID renseigné n'existe
                    } else if ($id == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin portant l'ID : " . $medRef);
                    // Sinon, aucune modification n'a eu lieu
                    } else {
                        fournirReponse("Erreur", 409, "Usager d'ID : ".$id." inchangé car il contient déjâ ces informations");
                    }

                // Sinon, on la crée
                } else {
                    // Récupération de l'ID ou du code d'erreur de l'usager ajouté
                    $id = addUsager($pdo, $arguments["civilite"], $arguments["sexe"], $arguments["nom"], $arguments["prenom"], 
                                    $arguments["adresse"], $arguments["ville"], $arguments["code_postal"], $arguments["num_secu"],
                                    $arguments["date_nais"], $arguments["lieu_nais"], $medRef);

                    // Si l'ID est positif, alors l'usager a bien été ajouté
                    if ($id > 0) {
                        $usager = getUsagerById($pdo, $id);
                        fournirReponse("Succes", 201, "Usager crée", $usager);
                    // Sinon, si le code d'erreur vaut -1, un usager avec le numéro de sécurité sociale renseigné existe déjâ
                    } else if ($id == -1) {
                        fournirReponse("Erreur", 422, "Un usager portant ce numéro de sécurité sociale existe déjâ");
                    // Sinon, si le code d'erreur vaut -2, aucun médecin avec l'ID renseigné n'existe
                    } else if ($id == -2) {
                        fournirReponse("Erreur", 422, "Aucun médecin portant l'ID : " . $medRef);
                    // Sinon, une autre erreur a eu lieu
                    } else {
                        fournirReponse("Erreur", 400, "Une erreur est survenue, l'usager n'a pas été crée");
                    }
                }

            // Sinon, si au moins un champ n'a pas été défini
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
 * Vérifie que tous les champs non-null fournis sont valide avec des vérifications
 * propres à chaque champ, et retourne une erreur et arrête le script le cas échéant
 *
 * @param mixed $civilite       Civilité de l'usager
 * @param mixed $nom            Nom de l'usager
 * @param mixed $prenom         Prénom de l'usager
 * @param mixed $sexe           Sexe de l'usager
 * @param mixed $adresse        Adresse de l'usager
 * @param mixed $ville          Ville de l'usager
 * @param mixed $codePostal     Code postal de l'usager
 * @param mixed $numSecu        Numéro de sécurité sociale de l'usager
 * @param mixed $dateNaissance  Date de naissance de l'usager
 * @param mixed $lieuNaissance  Ville de naissance de l'usager
 * @param mixed $medRef         ID du médecin référent de l'usager
 * @return void
 */
function verifierChamps(mixed $civilite, mixed $nom, mixed $prenom, mixed $sexe, mixed $adresse, mixed $ville, 
                        mixed $codePostal, mixed $numSecu, mixed $dateNaissance, mixed $lieuNaissance, mixed $medRef) {
    if (isset($civilite) && (tailleChampPlusGrandeQue($civilite, 4) || !contientLettresUniquement($civilite))) {
        fournirReponse("Erreur", 422, "La civilité de l'usager est invalide");
        exit();
    } else if (isset($nom) && (tailleChampPlusGrandeQue($nom, 50) || !contientLettresUniquement($nom))) {
        fournirReponse("Erreur", 422, "Le nom de l'usager est invalide");
        exit();
    } else if (isset($prenom) && (tailleChampPlusGrandeQue($prenom, 50) || !contientLettresUniquement($prenom))) {
        fournirReponse("Erreur", 422, "Le prénom de l'usager est invalide");
        exit();
    } else if (isset($sexe) && (!tailleChampRespectee($sexe, 1) || !contientLettresUniquement($sexe))) {
        fournirReponse("Erreur", 422, "Le sexe de l'usager est invalide (M ou F)");
        exit();
    } else if (isset($adresse) && tailleChampPlusGrandeQue($adresse, 100)) {
        fournirReponse("Erreur", 422, "L'adresse de l'usager est trop longue (Max 100 caractères)");
        exit();
    } else if (isset($ville) && (tailleChampPlusGrandeQue($ville, 50) || !contientLettresUniquement($ville))) {
        fournirReponse("Erreur", 422, "La ville de l'usager est invalide");
        exit();
    } else if (isset($codePostal) && (!tailleChampRespectee($codePostal, 5) || !nombrePositif($codePostal))) {
        fournirReponse("Erreur", 422, "Le code postal de l'usager est invalide");
        exit();
    } else if (isset($numSecu) && (!tailleChampRespectee($numSecu, 15) || !nombrePositif($numSecu))) {
        fournirReponse("Erreur", 422, "Le numéro de sécurité sociale de l'usager est invalide");
        exit();
    } else if (isset($dateNaissance) && (!formatDateCorrect($dateNaissance, 'd/m/Y'))) {
        fournirReponse("Erreur", 422, "La date de naissance de l'usager est invalide");
        exit();
    } else if (isset($lieuNaissance) && (tailleChampPlusGrandeQue($lieuNaissance, 50) || !contientLettresUniquement($lieuNaissance))) {
        fournirReponse("Erreur", 422, "La ville de naissance de l'usager est invalide");
        exit();
    } else if (isset($medRef) && !nombrePositif($medRef)) {
        fournirReponse("Erreur", 422, "L'ID du médecin référent de l'usager est invalide");
        exit();
    }
}