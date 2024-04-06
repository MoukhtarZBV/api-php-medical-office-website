<?php

function verificationJeton($jwt) : mixed {
	if (empty($jwt)) {
		return false;
	}
    $url = 'https://medical-office.alwaysdata.net/api/API_auth.php';

	
    $authorization = "Authorization: Bearer ".$jwt;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true)["donnees"];
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

function isAdmin($jwt) {
	return $jwt["role"] == "admin";
}

function isMedecin($jwt) {
	return $jwt["role"] == "medecin";
}

function isUsager($jwt) {
	return $jwt["role"] == "usager";
}

function isRightMedecin($idMedecin, $jwt) {
	return $jwt["role"] == "medecin" && $jwt["id"] == $idMedecin;
}

function isRightUsager($idUsager, $jwt) {
	return $jwt["role"] == "usager" && $jwt["id"] == $idUsager;
}

function contientEspace(string $champ) {
	return !empty($champ) && str_contains($champ, ' ');
}

function contientLettresUniquement(string $champ) {
	// Le regex ci-dessous vérifie que le champ est constitué 
	// uniquement de lettres, accents inclus, et d'un point ou d'un tiret
	return !empty($champ) && preg_match('/^[\p{L}\p{M}.-]+$/u', $champ);
}

function tailleChampRespectee(string $champ, int $taille){
	return !empty($champ) && strlen($champ) == $taille;
}

function tailleChampPlusGrandeQue(string $champ, int $taille){
	return !empty($champ) && strlen($champ) > $taille;
}

function nombrePositif(mixed $champ) {
	return !empty($champ) && ((is_string($champ) && ctype_digit($champ) && $champ > 0) || is_int($champ) && $champ > 0);
}

function formatDateCorrect(mixed $date, string $format = 'Y-m-d') {
	$dateTemp = DateTime::createFromFormat($format, $date);
	return $dateTemp && $dateTemp->format($format) == $date;
}

function formatHeureCorrect(mixed $heure, string $format = 'H:i') {
	$heureTemp = DateTime::createFromFormat($format, $heure);
	return $heureTemp && $heureTemp->format($format) == $heure;
}