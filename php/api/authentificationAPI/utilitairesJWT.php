<?php

function generate_jwt($headers, $payload, $secret) {
	$headers_encoded = base64url_encode(json_encode($headers));

	$payload_encoded = base64url_encode(json_encode($payload));

	$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
	$signature_encoded = base64url_encode($signature);

	$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
	
	return $jwt;
}

function is_jwt_valid($jwt, $secret) {
	// split the jwt
	$tokenParts = explode('.', $jwt);
	//print_r($tokenParts);
	$header = base64_decode($tokenParts[0]);
	$payload = base64_decode($tokenParts[1]);
	$signature_provided = $tokenParts[2];

	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
	$expiration = json_decode($payload)->exp;
	$is_token_expired = ($expiration - time()) < 0;

	// build a signature based on the header and payload using the secret
	$base64_url_header = base64url_encode($header);
	$base64_url_payload = base64url_encode($payload);
	$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
	$base64_url_signature = base64url_encode($signature);

	// verify it matches the signature provided in the jwt
	$is_signature_valid = ($base64_url_signature === $signature_provided);

	if ($is_token_expired || !$is_signature_valid) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
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
