<?php
    require ("jwt_utils.php");
    require ("../connexionDB.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData,true); 
        if (!empty($data["login"]) && !empty($data["password"])) {
            if ($data["login"] == "CABINET" && $data["password"] == "CABINET") {
                $headers = array('alg'=>'HS256', 'typ'=>'JWT');
                $payload = array('username'=>$data["login"], 'exp'=>(time() + 60), 'role'=>$user["role"]);
                deliver_response(200, "Login success", generate_jwt($headers, $payload, 'CabinetMed'));
            } else {
                deliver_response(400, "Login error", "null");
            }
        }
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

    function getUser(PDO $pdo, string $login, string $password) : array | null {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE login = ? AND password = ?");
        $stmt->execute([$login, $password]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }