<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleEquipe = clean_mysql_string($mysqli, $_POST['equipe']);
    $newNom = clean_mysql_string($mysqli, $_POST['nom']);
    $newCouleur = clean_mysql_string($mysqli, $_POST['couleur']);
    $newCle = genereCle($newNom);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que l'equipe existe et recupere son id.
    $selectEquipeQuery = "SELECT ID, NOM FROM FR_EQUIPE WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleEquipe . "';";
    $dbEquipeResult = query_mysql($mysqli, $selectEquipeQuery, "Impossible de trouver l'equipe avec la cle '" . $cleEquipe . "'.", true);
    $equipeId = $dbEquipeResult[0]["ID"];
    $equipeNom = $dbEquipeResult[0]["NOM"];

    // Met a jour l'equipe
    $updateQuery = "UPDATE FR_EQUIPE SET CLE = '" . $newCle . "', NOM = '" . $newNom . "', COULEUR = '" . $newCouleur . "' WHERE ID = " . $equipeId . ";";
    query_mysql($mysqli, $updateQuery, "Impossible de modifier l'equipe '" . $equipeNom . "'.");

    $response->message = "L'equipe '" . $equipeNom . "' a ete modifie.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>