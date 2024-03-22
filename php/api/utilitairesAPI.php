<?php

function jetonValide($jwt) {
    $url = 'http://localhost/api-php-medical-office-website/php/api/authentificationAPI/authAPI.php?jwt=' . urlencode($jwt);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result)->donnees;
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

function get_authorization_header(){
	$headers = null;

	if (isset($_SERVER['Authorization'])) {
		$headers = trim($_SERVER["Authorization"]);
	} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
		$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	} else if (function_exists('apache_request_headers')) {
		$requestHeaders = apache_request_headers();
		// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
		$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
		//print_r($requestHeaders);
		if (isset($requestHeaders['Authorization'])) {
			$headers = trim($requestHeaders['Authorization']);
		}
	}

	return $headers;
}

function get_bearer_token() {
    $headers = get_authorization_header();
    
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            if($matches[1]=='null') //$matches[1] est de type string et peut contenir 'null'
                return null;
            else
                return $matches[1];
        }
    }
    return null;
}

function get_role($jwt) {
	// split the jwt
	$tokenParts = explode('.', $jwt);
	//print_r($tokenParts);
	$header = base64_decode($tokenParts[0]);
	$payload = base64_decode($tokenParts[1]);
	$signature_provided = $tokenParts[2];

	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
	return json_decode($payload)->role;
}

function get_id($jwt) {
	// split the jwt
	$tokenParts = explode('.', $jwt);
	//print_r($tokenParts);
	$header = base64_decode($tokenParts[0]);
	$payload = base64_decode($tokenParts[1]);
	$signature_provided = $tokenParts[2];

	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
	return json_decode($payload)->idUtilisateur;
}