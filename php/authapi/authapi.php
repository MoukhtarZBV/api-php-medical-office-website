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
        if (getUser($pdo,$data['username'],$data['password'])) {
            $user = getUser($pdo,$data['username'],$data['password']);
            $headers = array('alg'=>('HS256'),'typ'=>'JWT');
            $payload = array('username'=>$data['username'], 'exp'=>(time()+60), 'role'=>$user['role']);
            $jwt = generate_jwt($headers,$payload,"secret");
            deliver_response(200, "Welcome!", $jwt);
        } else {
            deliver_response(401, "Incorrect logins, try again!", null);
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

    function deliver_response($status_code, $status_message, $data = null)
    {
        http_response_code($status_code);
        header("Content-Type:application/json; charset=utf-8");
        $response['status_code'] = $status_code;
        $response['status_message'] = $status_message;
        $response['data'] = $data;
        $json_response = json_encode($response);
        if ($json_response === false) {
            die('json encode ERROR : ' . json_last_error_msg());
        }
        echo $json_response;
    }

?>