<?php

require("../utils/utilitaires.php");

/**
 * Renvoie le médecin d'identifiant idMedecin 
 * 
 * @param PDO $pdo          PDO permettant la connexion à la BDD
 * @param int $idMedecin    identifiant du médecin recherché 
 * 
 * @return array l'array décrivant le médecin, possiblement vide si aucun n'est trouvé 
 */
function getMedecinById(PDO $pdo, int $idMedecin) : array {

    //Préparation de la requête 
    $stmt = $pdo->prepare("SELECT * FROM medecin WHERE idMedecin = ?");

    //Exécution de la requête 
    if (!$stmt) {
        return array();
    }
    if ($stmt->execute([$idMedecin])) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return array();

}

/**
 * Récupération de tous les médecins, possiblement en appliquant des filtres 
 * 
 * @param PDO $pdo              PDO permettant la connexion à la BDD
 * @param string|null $civilite possible filtre correspondant à la civilité
 * @param string|null $nom      possible filtre correspondant au nom
 * @param string|null $prenom   possible filtre correspondant au prénom
 * 
 * @return array|bool renvoie l'array de médecins ou alors le booléen false en cas d'erreur 
 */
function getMedecins(PDO $pdo, string | null $civilite, string | null $nom, string | null $prenom) : array | bool {

    //Préparation de la requête 
    $sql = "SELECT idMedecin, civilite, nom, prenom FROM medecin";

    // Ajout des potentiels filtres
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? " AND " : " WHERE ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }
        
    // Exécution de la requête
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
 * Ajoute un médecin à la bdd
 * 
 * @param PDO $pdo          PDO permettant la connexion à la BDD
 * @param string $civilite  la civilité du médecin
 * @param string $nom       le nom du médecin
 * @param string $prenom    le prénom du médecin
 * 
 * @return int|null renvoie l'identifiant du médecin créé OU un null si rien n'a été créé
 */
function addMedecin(PDO $pdo, string $civilite, string $nom, string $prenom) : int | null {

    //Préparation de la requête 
    $stmt = $pdo->prepare("INSERT INTO medecin(civilite, nom, prenom) VALUES (?, ?, ?)");
    if (!$stmt) {
        return null;
    }

    //Exécution de la requête et récupération de l'identifiant du médecin ajouté 
    $pdo->beginTransaction();
    $idMedecin = $stmt->execute([$civilite, $nom, $prenom]) ? $pdo->lastInsertId() : null;
    $pdo->commit();
    return $idMedecin;
}

/**
 * Supprime un médecin à l'aide de son identifiant 
 * 
 * @param PDO $pdo          PDO permettant la connexion à la BDD
 * @param int $idMedecin    identifiant du médecin à supprimer
 * 
 * @return bool renvoie un booléen indiquant si la suppression a été effectuée ou non
 */
function deleteMedecin(PDO $pdo, int $idMedecin) : bool {

    //préparation de la requête
    $stmt = $pdo->prepare("DELETE FROM medecin WHERE idMedecin = ?");
    if (!$stmt) {
        return false;
    }

    //exécution de la requête
    if ($stmt->execute([$idMedecin])) {
        return $stmt->rowCount() > 0;
    }
    return false;

}

/**
 * Met à jour un médecin à l'aide de son identifiant 
 * 
 * @param PDO $pdo 
 * @param int $idMedecin        identifiant du médecin à modifier
 * @param string|null $civilite potentielle civilité à modifier
 * @param string|null $nom      potentiel nom à modifier
 * @param string|null $prenom   potentiel prénom à modifier
 * 
 * @return bool renvoie un booléen indiquant si la modification a été effectuée ou non
 */

function editMedecin(PDO $pdo, int $idMedecin, string | null $civilite, string | null $nom, string | null $prenom) : bool {

    //déclaration de la requête 
    $sql = "UPDATE medecin SET";

    //récupération des champs à modifier 
    $criteres = ["civilite" => $civilite, "nom" => $nom, "prenom" => $prenom];
    $arguments = array();
    $unCritere = false;
    foreach ($criteres as $cle => $valeur) {
        if (!empty($valeur)) {
            $sql .= ($unCritere ? ", " : " ") . $cle . " = :" . $cle;
            $arguments[$cle] = $valeur;
            $unCritere = true;
        }
    }

    //ajout de la clause de recherche du médecin à modifier 
    if (!$unCritere) { return false; }
    $arguments["idMedecin"] = $idMedecin;
    $sql .= " WHERE idMedecin = :idMedecin";

    //préparation et exécution de la requête 
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        return false;
    }
    return $stmt->execute($arguments);

}