<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $nom = clean_mysql_string($mysqli, $_POST['nom']);

    createDatabaseIfNecessary($mysqli);

    // Determine une cle pour creer le sejour. 
    $count = 1;
    $cle = "";
    $index = 0;
    while ($count > 0 && $index < 1000) {
        $cle = genereCleSejour($nom);
        $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cle . "';";
        $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de consulter la liste des sejours existants.");
        $count = count($dbSejourResult);
        $index++;
    }

    if ($index == 1000) {
        throw new Exception("Impossible de creer le sejour '" . $nom . "'. Ce nom a trop souvent ete utilise.");
    }

    // Insere le sejour avec la cle generee
    $insertSejourQuery = "INSERT INTO FR_SEJOUR (CLE, NOM) VALUES ('" . $cle . "', '" . $nom . "');";
    query_mysql($mysqli, $insertSejourQuery, "Impossible de creer le sejour '" . $nom . "'");

    $response->message = "Le sejour '" . $nom . "' a ete cree.";
    $response->content = array("cle" => $cle);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>