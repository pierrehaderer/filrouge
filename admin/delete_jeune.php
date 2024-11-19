<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleJeune = clean_mysql_string($mysqli, $_POST['jeune']);

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que le jeune existe et recupere son id. 
    $selectJeuneQuery = "SELECT ID, NOM, PRENOM, PHOTO FROM FR_JEUNE WHERE CLE = '" . $cleJeune . "'AND SEJOUR_ID = " . $sejourId . ";";
    $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver le jeune avec la cle '" . $cleJeune . "'.", true);
    $jeuneId = $dbJeuneResult[0]["ID"];
    $jeuneNom = $dbJeuneResult[0]["NOM"];
    $jeunePrenom = $dbJeuneResult[0]["PRENOM"];

    // Supprime les defis valides par le jeune
    $deleteDefisQuery = "DELETE FROM FR_JEUNE_DEFI WHERE JEUNE_ID = " . $jeuneId . ";";
    query_mysql($mysqli, $deleteDefisQuery, "Impossible de supprimer les defis valides par '" . $jeuneNom . " " . $jeunePrenom . "'.");

    // Supprime le jeune
    $deleteJeuneQuery = "DELETE FROM FR_JEUNE WHERE ID = " . $jeuneId . ";";
    query_mysql($mysqli, $deleteJeuneQuery, "Impossible de supprimer '" . $jeuneNom . " " . $jeunePrenom . "'.");

    $response->message = "Le jeune '" . $jeuneNom . " " . $jeunePrenom . "' a ete supprime.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>