<?php

    enum RetourAPI {
        case SUCCES;
        case AUCUNE_LIGNE;
        case ERREUR;
    }

    $urlRoot = "http://localhost/api-php-medical-office-website/";
    $urlAPI = "http://localhost/api-php-medical-office-website/php/api/implementationsAPI/";
    $urlAPIMedecins = $urlAPI . 'API_medecins.php';
    $urlAPIUsagers = $urlAPI . 'API_usagers.php';
    $urlAPIConsultations = $urlAPI . 'API_consultations.php';

    function returnStatut($code, $donnees) {
        if (200 <= $code && $code < 300) {
            return $donnees;
        } else {
            return 0;
        }
    }

    function verifierAuthentification(){
        if (!isset($_SESSION['utilisateur']) || empty($_SESSION['utilisateur'])){
            header('Location: authentification.php'); exit();
        }
    }

    function verifierPrepare($stmt){
        if (!$stmt) { 
            echo "Erreur lors d'un prepare statement : " . $stmt->errorInfo(); exit(); 
        }
    }

    function verifierExecute($stmt){
        if (!$stmt) {
            echo "Erreur lors d'un execute statement : " . $stmt->errorInfo(); exit(); 
        }
    }

    function formaterDate($date){
        $elementsDate = explode('-', $date);
        $dateFormatee = $elementsDate[2] . '/' . $elementsDate[1] . '/' . $elementsDate[0];
        return $dateFormatee;
    }

    function ajouterParamsURL(string | null $nom, string | null $prenom) : string {
        $url = "";
        $premierFiltre = true;
        if (!empty($civilite)) {
            $url .= ($premierFiltre ? '?' : '&') . 'civilite=' . $civilite;
            $premierFiltre = false;
        }
        if (!empty($nom)) {
            $url .= ($premierFiltre ? '?' : '&') . 'nom=' . $nom;
            $premierFiltre = false;
        }
        if (!empty($prenom)) {
            $url .= ($premierFiltre ? '?' : '&') . 'prenom=' . $prenom;
        } 
        return $url;
    }
?>