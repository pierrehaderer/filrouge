<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleAnimateur = clean_mysql_string($mysqli, $_POST['animateur']);
    $newNom = clean_mysql_string($mysqli, $_POST['nom']);
    $newCle = genereCle($newNom);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que l'animateur existe et recupere son id.
    $selectAnimQuery = "SELECT ID, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleAnimateur . "';";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec la cle '" . $cleAnimateur . "'.", true);
    $animateurId = $dbAnimResult[0]["ID"];
    $animateurNom = $dbAnimResult[0]["NOM"];

    // Met a jour l'animateur
    $updateQuery = "UPDATE FR_ANIMATEUR SET CLE = '" . $newCle . "', NOM = '" . $newNom . "' WHERE ID = " . $animateurId . ";";
    query_mysql($mysqli, $updateQuery, "Impossible de modifier l'animateur '" . $animateurNom . "'.");

    $response->message = "L'animateur '" . $animateurNom . "' a ete modifie.";
    $response->content = array("animateur" => array("CLE" => $newNom, "NOM" => $newCle));

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>