<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleAnimateur = clean_mysql_string($mysqli, $_POST['animateur']);

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que l'animateur existe et recupere son id.
    $selectAnimQuery = "SELECT ID, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleAnimateur . "';";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec la cle '" . $cleAnimateur . "'.", true);
    $animateurId = $dbAnimResult[0]["ID"];
    $animateurNom = $dbAnimResult[0]["NOM"];

    // Recupere les defis de cet animateur. 
    $selectDefisQuery = "SELECT ID, DESCRIPTION, TYPE FROM FR_DEFI WHERE ANIMATEUR_ID = " . $animateurId . " AND ACTIF = 1 ORDER BY ID ASC;";
    $dbDefisResult = query_mysql($mysqli, $selectDefisQuery, "Impossible de trouver les defis de l'animateur '" . $animateurNom . "'.");

    $response->content = array("animateur" => $dbAnimResult[0], "defis" => $dbDefisResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>