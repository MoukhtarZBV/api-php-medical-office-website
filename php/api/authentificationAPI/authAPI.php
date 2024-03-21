<?php

    require("utilitairesJWT.php");
    require("../../db/connexionDB.php");

    $_SECRET_KEY = "SECRET_KEY";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //recuperation du body de la requête POST
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 

        //verif de la validité du login
        $pdo = createConnection();

        //si les logins sont valides, alors on va fournir un jeton à l'utilisateur
        if (getUser($pdo,$data['login'],$data['password'])) {
            $user = getUser($pdo,$data['login'],$data['password']);
            $headers = array('alg'=>('HS256'),'typ'=>'JWT');
            $payload = array('login'=>$data['login'], 'exp'=>(time()+1980), 'role'=>$user['role']);
            $jwt = generate_jwt($headers, $payload, $_SECRET_KEY);
            fournirReponse("Succes", 200, "Login réussi", $jwt);
        } else {
            fournirReponse("Erreur", 401, "Login et/ou mot de passe incorrect(s)", null);
        }
        
    } else if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (!empty($_GET["jwt"])) {
            fournirReponse("Succes", 200, "Vérification du jeton effectuée", is_jwt_valid($_GET["jwt"], $_SECRET_KEY));
        } else {
            fournirReponse("Erreur", 400, "Vérification du jeton échouée");
        }
    }

    function getUser(PDO $pdo, string $login, string $password) {
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
