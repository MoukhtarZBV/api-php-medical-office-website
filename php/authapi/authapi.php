<?php

    require ("jwt_utils.php");
    require ("connexionDB.php");

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
            $payload = array('login'=>$data['login'], 'exp'=>(time()+60), 'role'=>$user['role']);
            $jwt = generate_jwt($headers,$payload,"secret");
            fournirReponse("Succes", 200, "Login réussi", $jwt);
        } else {
            fournirReponse("Erreur", 401, "Login et/ou mot de passe incorrect(s)", null);
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

    function fournirReponse(string $statut, string $statutCode, string $statutMessage, mixed $donnees = null) : void {
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

?>
