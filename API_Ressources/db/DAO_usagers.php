<?php

require("../utils/utilitaires.php");

/**
 * Renvoie l'usager d'identifiant idUsager dans la bdd liée au PDO pdo
 * sous forme d'un array contenant tous ses champs ou un array vide en cas d'erreur
 * 
 * @param PDO $pdo      le pdo de la connexion à la bdd
 * @param int $idUsager l'identifiant de l'usager à trouver
 * 
 * @return array|bool        un array décrivant l'usager trouvé OU false si aucun usager n'a été récupéré 
 */
function getUsagerById(PDO $pdo, int $idUsager) : array|bool {

    //préparation de la requête 
    $stmt = $pdo->prepare("SELECT usager.*, DATE_FORMAT(usager.dateNaissance, '%d/%m/%Y') as dateNaissance, medecin.nom as nomMedecin, medecin.prenom as prenomMedecin 
                            FROM usager 
                            LEFT JOIN medecin ON usager.medecinReferent = medecin.idMedecin
                            WHERE idUsager = ?");

    if ($stmt && $stmt->execute([$idUsager])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return false;
    
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
 * @return array|bool renvoie l'array de tous les usagers OU null s'il y a eu une erreur 
 * 
 */
function getUsagers(PDO $pdo, string | null $civilite, string | null $nom, 
string | null $prenom, string | null $numSS) : array | null {

    //déclaration de la requête 
    $sql = "SELECT usager.*, DATE_FORMAT(usager.dateNaissance, '%d/%m/%Y') as dateNaissance, medecin.nom as nomMedecin, medecin.prenom as prenomMedecin FROM usager";

    // ajout de potentiels filtres de recherche  
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom, "numeroSecuriteSociale" => $numSS];
    $arguments = array();
    $unCritere = false;
    
    $sql .= " LEFT JOIN medecin ON usager.medecinReferent = medecin.idMedecin ";

    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? " AND " : " WHERE ") . "usager." . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
  
    //Execution de la requête 
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($arguments);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $Exception ) {
        return null;
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
function addUsager(PDO $pdo, string $civilite, string $sexe, string $nom, string $prenom, string $adresse, string $ville,
    string $codePostal, string $numeroSecuriteSociale, string $dateNaissance, string $lieuNaissance, int | null $medecinReferent) : int {

     //Exécution de la requête et récupération de l'identifiant de l'usager ajouté 
    $pdo->beginTransaction(); 
    try {
        $stmt = $pdo->prepare("INSERT INTO usager(civilite, sexe, nom, prenom, adresse, ville, codePostal, numeroSecuriteSociale, dateNaissance, lieuNaissance, medecinReferent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $dateFormatee = date('Y-m-d', strtotime(str_replace('/', '-', $dateNaissance)));
        $idUsager = $stmt->execute([$civilite, $sexe, $nom, $prenom, $adresse, $ville, $codePostal, $numeroSecuriteSociale, $dateFormatee, $lieuNaissance, $medecinReferent]) ? $pdo->lastInsertId() : null;
        $pdo->commit(); 
        return $idUsager;
    } catch (PDOException $exception) {
        // Violation de la contrainte d'unicité du numéro de sécurité sociale
        if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1062') !== false) {
            return -1;
        // Violation de la contrainte de clé étrangère pour le médecin référent
        } else if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1452') !== false) {
            return -2;
        } else {
            return 0;
        }
    }

}

/**
 * Supprime l'usager de l'identifiant donné
 * 
 * @param PDO $pdo      La connexion à la bdd 
 * @param int $idUsager L'identifiant de l'usager à supprimer 
 * 
 * @return bool         renvoie un booléen indiquant si la suppression s'est faite 
 */

function deleteUsager(PDO $pdo, int $idUsager) : bool {
    try {
        //Préparation & exécution de la requête
        $stmt = $pdo->prepare("DELETE FROM usager WHERE idUsager = ?");
        $stmt->execute([$idUsager]);
        return $stmt->rowCount() > 0;
    } catch(PDOException $Exception ) {
        return false;
    }
}

/**
 * Modifier l'usager de l'identifiant donné avec les possibles différents champs
 * 
 * @param PDO $pdo                           Le pdo de connexion à la bdd
 * @param int $idUsager                      l'identifiant de l'usager à modifier
 * @param string|null $civilite              la civilité de l'usager si on veut la modifier
 * @param string|null $civilite              le sexe de l'usager si on veut le modifier
 * @param string|null $nom                   le nom de l'usager si on veut le modifier
 * @param string|null $prenom                le prénom de l'usager si on veut le modifier
 * @param string|null $adresse               l'adresse de l'usager si on veut la modifier 
 * @param string|null $ville                 la ville d'habitation de l'usager si on veut la modifier 
 * @param string|null $codePostal            le code postal de l'usager si on veut le modifier
 * @param string|null $numeroSecuriteSociale le numéro de sécurité sociale de l'usager si on veut le modifer
 * @param string|null $dateNaissance         la date de naissance de l'usager si on veut la modifier 
 * @param string|null $lieuNaissance         le lieu de naissance de l'usager si on veut le modifier
 * @param int|null $medecinReferent          l'identifiant du médecin référent de l'usager si on veut le modifer
 * 
 * @return bool renvoie un booléen indiquant si la modification a été faite ou non
 */
function editUsager(PDO $pdo, int $idUsager, string | null $civilite, string | null $sexe, string | null $nom, string | null $prenom, 
    string | null $adresse, string | null $ville, string | null $codePostal, string | null $numeroSecuriteSociale, 
    string | null $dateNaissance, string | null $lieuNaissance, int | null $medecinReferent) : int {
    
    //récupération de tous les arguments que l'on veut modifier et préparation de la requête en conséquence
    $sql = "UPDATE usager SET";
    $criteres = ["idUsager" => $idUsager, "civilite" => $civilite, "sexe" => $sexe, "nom" => $nom, "prenom" => $prenom, "adresse" => $adresse, "ville" => $ville,
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

    //ajout de la clause de recherche de l'usager
    if (!$unCritere) { return 0; }

    $arguments["idUsager"] = $idUsager;
    $sql .= " WHERE idUsager = :idUsager";
 
    try {
        //Préparation & exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($arguments);
        return $stmt->rowCount();
    } catch (PDOException $exception ) {
        // Violation de la contrainte d'unicité du numéro de sécurité sociale
        if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1062') !== false) {
            return -1;
        // Violation de la contrainte de clé étrangère pour le médecin référent
        } else if ($exception->getCode() == '23000' && strpos($exception->getMessage(), '1452') !== false) {
            return -2;
        } else {
            return 0;
        }
    }
  
}