<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleAnimateur = clean_mysql_string($mysqli, $_POST['animateur']);
    $defiId = clean_mysql_string($mysqli, $_POST['defi']);
    $newDescription = clean_mysql_string($mysqli, $_POST['description']);
    $newType = clean_mysql_string($mysqli, $_POST['type']);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que l'animateur existe et recupere son id.
    $selectAnimQuery = "SELECT ID, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleAnimateur . "';";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec la cle '" . $cleAnimateur . "'.", true);
    $animateurId = $dbAnimResult[0]["ID"];

    // Verifie que le defi existe et recupere son ancienne description.
    $selectDefiQuery = "SELECT ID, DESCRIPTION FROM FR_DEFI WHERE ID = '" . $defiId . "' AND ANIMATEUR_ID = " . $animateurId . ";";
    $dbDefiRresult = query_mysql($mysqli, $selectDefiQuery, "Impossible de trouver le defi avec l'id '" . $defiId . "'.", true);
    $description = $dbDefiRresult[0]["DESCRIPTION"];

    // Met a jour le defi
    $updateQuery = "UPDATE FR_DEFI SET DESCRIPTION = '" . $newDescription . "', TYPE = '" . $newType . "'WHERE ID = " . $defiId . ";";
    query_mysql($mysqli, $updateQuery, "Impossible de modifier le defi '" . getSummary($description) . "'.");

    $response->message = "Le defi '" . getSummary($description) . "' a ete modifie.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>