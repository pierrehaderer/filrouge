<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $newNom = clean_mysql_string($mysqli, $_POST['nom']);
    $newMethode = clean_mysql_string($mysqli, $_POST['methode']);
    $newEquipe = clean_mysql_string($mysqli, $_POST['equipe']);
    $newTableau = clean_mysql_string($mysqli, $_POST['tableau']);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID, NOM FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];
    $sejourNom = $dbSejourResult[0]["NOM"];

    // Met a jour le sejour
    $updateQuery = "UPDATE FR_SEJOUR SET NOM = '" . $newNom . "', METHODE = '" . $newMethode . "', EQUIPE = '" . $newEquipe . "', TABLEAU = '" . $newTableau . "' WHERE ID = " . $sejourId . ";";
    query_mysql($mysqli, $updateQuery, "Impossible de modifier le sejour '" . $sejourNom . "'.");

    $response->message = "Le sejour '" . $sejourNom . "' a ete modifie.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>