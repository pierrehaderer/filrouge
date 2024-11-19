<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $nom = clean_mysql_string($mysqli, $_POST['nom']);
    $couleur = clean_mysql_string($mysqli, $_POST['couleur']);
    $cle = genereCle($nom);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Insere l'animateur en l'attachant au sejour
    $insertQuery = "INSERT INTO FR_EQUIPE (CLE, NOM, COULEUR, SEJOUR_ID) VALUES ('" . $cle . "', '" . $nom . "', '" . $couleur . "', " . $sejourId . ");";
    query_mysql($mysqli, $insertQuery, "Impossible de creer l'equipe '" . $nom . "'.");

    $response->message = "L'equipe '" . $nom . "' a ete cree.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>