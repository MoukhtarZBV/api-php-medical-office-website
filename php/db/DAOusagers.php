<?php

require("../functions/fonctions.php");

/**
 * Renvoie l'usager d'identifiant idUsager dans la bdd liée au PDO pdo
 * sous forme d'un array contenant tous ses champs ou un array vide en cas d'erreur
 * 
 * @param PDO $pdo      le pdo de la connexion à la bdd
 * @param int $idUsager l'identifiant de l'usager à trouver
 * 
 * @return array        l'usager trouvé OU un array vide s'il y a une erreur 
 */
function getUsagerById(PDO $pdo, int $idUsager) : array {
    $stmt = $pdo->prepare("SELECT * FROM usager WHERE idUsager = ?");
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idUsager])) {
        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $res;
        }
    }
    return array();
}

/**
 * Récupère tous les usagers dans la bdd liée au PDO pdo, possibilement à l'aide
 * de critères de recherche (civilité, nom, prénom, numéro de sécurité sociale),
 * renvoie un array d'usagers OU le booléen false s'il n'y a eu une erreur 
 * 
 * @param PDO $pdo              le pdo de connexion à la bdd
 * @param string|null $nom      le potentiel nom de l'usager
 * @param string|null $prenom   le potentiel prénom de l'usager
 * @param string|null $numSS    le potentiel numéro de sécurité sociale de l'usager
 * 
 * @return array|bool renvoie l'array de tous les usagers ou faux s'il y a eu une erreur 
 * 
 */
function getUsagers(PDO $pdo, string | null $civilite, string | null $nom, 
string | null $prenom, string | null $numSS) : array | bool {

    $sql = "SELECT * FROM usager";

    // Recherche en fonction des filtres
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom, "numeroSecuriteSociale" => $numSS];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? " AND " : " WHERE ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
        
    //Exécutuion du DAO
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute($arguments)) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        return false;
    }

}

/**
 * Ajoute un usager dans la bdd avec les arguments donnés
 * 
 * @param PDO $pdo                      Le pdo de connexion à la bdd
 * @param string $civilite              la civilité de l'usager
 * @param string $nom                   le nom de l'usager
 * @param string $prenom                le prénom de l'usager
 * @param string $adresse               l'adresse de l'usager
 * @param string $ville                 la ville d'habitation de l'usager
 * @param string $codePostal            le code postal de l'usager
 * @param string $numeroSecuriteSociale Le numéro de sécurité sociale de l'usager
 * @param string $dateNaissance         La date de naissance de l'usager
 * @param string $lieuNaissance         Le lieu de naissance de l'usager 
 * @param int|null $medecinReferent     L'identifiant du potentiel médecin référent de l'usager
 * 
 * @return int|null l'identifiant de l'usager inséré OU null s'il y a eu une erreur
 */
function addUsager(PDO $pdo, string $civilite, string $nom, string $prenom, string $adresse, string $ville,
    string $codePostal, string $numeroSecuriteSociale, string $dateNaissance, string $lieuNaissance, int | null $medecinReferent) : int | null {

    $stmt = $pdo->prepare("INSERT INTO usager(civilite, nom, prenom, adresse, ville, codePostal, numeroSecuriteSociale, dateNaissance, lieuNaissance, medecinReferent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return null;
    }

    $pdo->beginTransaction(); $idUsager = 0;
    //récupère si la requête a été exécutée l'identifiant inséré par la requête
    try {
        $idUsager = $stmt->execute([$civilite, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale, $dateNaissance, $lieuNaissance, $medecinReferent]) ? $pdo->lastInsertId() : null;
        $pdo->commit();
    }
    catch(PDOException $Exception ) {
        return null;
    }

    return $idUsager;

}

/**
 * Supprime l'usager de l'identifiant donné
 * 
 * @param PDO $pdo      La connexion à la bdd 
 * @param int $idUsager L'identifiant de l'usager à supprimer 
 * 
 * @return bool renvoie un booléen indiquant si la suppression s'est faite 
 */

function deleteUsager(PDO $pdo, int $idUsager) : bool {

    $stmt = $pdo->prepare("DELETE FROM usager WHERE idUsager = ?");
    if (!$stmt) {
        return false;
    }
    if ($stmt->execute([$idUsager])) {
        //renvoie si le nombre de suppressions faites est > à 0 
        return $stmt->rowCount() > 0;
    }
    return false;
    
}

/**
 * Modifier l'usager de l'identifiant donné avec les possibles différents champs
 * 
 * @param PDO $pdo                             Le pdo de connexion à la bdd
 * @param int $idUsager                      L'identifiant de l'usager à modifier
 * @param string|null $civilite              la civilité de l'usager si on veut la modifier
 * @param string|null $nom                   le nom de l'usager si on veut le modifier
 * @param string|null $prenom                le prénom de l'usager si on veut le modifier
 * @param string|null $adresse               l'adresse de l'usager si on veut la modifier 
 * @param string|null $ville                 la ville d'habitation de l'usager si on veut la modifier 
 * @param string|null $codePostal            le code postal de l'usager si on veut le modifier
 * @param string-null $numeroSecuriteSociale Le numéro de sécurité sociale de l'usager si on veut le modifer
 * @param string|null $dateNaissance         La date de naissance de l'usager si on veut la modifier 
 * @param string-null $lieuNaissance         Le lieu de naissance de l'usager si on veut le modifier
 * @param int|null $medecinReferent          L'identifiant du médecin référent de l'usager si on veut le modifer
 * 
 * @return bool renvoie un booléen indiquant si la modification a été faite ou non
 */
function editUsager(PDO $pdo, int $idUsager, string | null $civilite, string | null $nom, string | null $prenom, 
    string | null $adresse, string | null $ville, string | null $codePostal, string | null $numeroSecuriteSociale, 
    string | null $dateNaissance, string | null $lieuNaissance, int | null $medecinReferent)  : bool {
    
    //récupération de tous les arguments que l'on veut modifier et préparation de la requête en conséquence
    $sql = "UPDATE usager SET";
    $criteres = ["idUsager" => $idUsager, "civilite" => $civilite, "nom" => $nom, "prenom" => $prenom, "adresse" => $adresse, "ville" => $ville,
    "codePostal" => $codePostal, "numeroSecuriteSociale" => $numeroSecuriteSociale, "dateNaissance" => $dateNaissance,
    "lieuNaissance" => $dateNaissance, "medecinReferent" => $medecinReferent];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? ", " : " ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
    if (!$unCritere) { return false; }
    $arguments["idUsager"] = $idUsager;
    $sql .= " WHERE idUsager = :idUsager";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }

    return $stmt->execute($arguments);
  
}