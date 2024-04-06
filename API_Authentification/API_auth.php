<?php

    require("utilitairesJWT.php");

    // Connexion à la base de données
    $pdo = new PDO("mysql:host=mysql-medical-office.alwaysdata.net;dbname=medical-office_auth", '350740', '$iutinfo');

    // Clé secrète pour créer un jeton
    $_SECRET_KEY = "SECRET_KEY";

    // Si on est en POST, alors une demande d'authentification a été faites
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Récupération des informations recues
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 

        // Si le login ou le mot de passe est vide, on retourne une erreur
        if (empty($data["login"]) || empty($data["mdp"])) {
            fournirReponse("Erreur", 422, "Login et/ou mot de passe non renseigné(s)");
        } else {
            // Si le login et le mot de passe sont valides, on fournit un jeton qui dure 6 heures à l'utilisateur
            if (getUser($pdo, $data['login'], $data['mdp'])) {
                $user = getUser($pdo,$data['login'],$data['mdp']);
                $headers = array('alg'=>('HS256'),'typ'=>'JWT');
                $payload = array('login'=>$data['login'], 'exp'=>(time()+21600), 'role'=>$user['role'], 'id' => $user['idUtilisateur']);
                $jwt = genererJeton($headers, $payload, $_SECRET_KEY);
                fournirReponse("Succes", 200, "Login réussi", $jwt);

            // Sinon, on retourne une erreur
            } else {
                fournirReponse("Erreur", 401, "Login et/ou mot de passe incorrect(s)");
            }
        }
    
    // Sinon, si on est en GET, alors une demande de validation du JWT a été faites
    } else if ($_SERVER["REQUEST_METHOD"] == "GET") {

        // On récupère le JWT et on vérifie sa validité s'il n'est pas vide
        $jwt = recupererToken();
        if (!empty($jwt)) {
            $valide = jetonValide($jwt, $_SECRET_KEY);

            // On renvoie les informations contenues dans le jeton ainsi que sa validité
            $data = array("valide" => $valide, "role" => recupererRole($jwt), "id" => recupererID($jwt));
            if ($valide) {
                fournirReponse("Succes", 200, "Jeton valide", $data);
            } else {
                fournirReponse("Erreur", 401, "Jeton invalide", $data);
            }

        // Sinon, on retourne une erreur
        } else {
            fournirReponse("Erreur", 404, "Aucun jeton trouvé");
        }
    }

    /**
     * Récupère les informations d'un utilisateur si le login et le mot de passe fournis sont corrects
     *
     * @param PDO $pdo          PDO établissant la connexion à la base de données
     * @param string $login     Login de l'utilisateur
     * @param string $password  Mot de passe de l'utilisateur
     * @return mixed            Un tableau contenant les informations de l'utilisateur ou NULL si aucun utilisateur trouvé
     */
    function getUser(PDO $pdo, string $login, string $password) : mixed {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE login = ?");
        $stmt->execute([$login]);
        if ($res = $stmt->fetch()) {
            if (password_verify($password,$res["password"])) {
                return $res;
            }
        }
        return null;     
    }

?>
