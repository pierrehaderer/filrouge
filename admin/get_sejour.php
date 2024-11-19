<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID, CLE, NOM, METHODE, EQUIPE, TABLEAU FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];
    $sejourNom = $dbSejourResult[0]["NOM"];

    // Recupere les animateurs de ce sejour. 
    $selectAnimsQuery = "SELECT CLE, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " ORDER BY NOM ASC;";
    $dbAnimsResult = query_mysql($mysqli, $selectAnimsQuery, "Impossible de trouver les animateurs du sejour '" . $sejourNom . "'");

    // Recupere les jeunes de ce sejour. 
    $selectJeunesQuery = "SELECT J.CLE, J.NOM, J.PRENOM, J.PHOTO, J.AGE, IFNULL(E.CLE, '') AS EQUIPE_CLE, E.NOM AS EQUIPE_NOM, IFNULL(E.COULEUR, '') AS EQUIPE_COULEUR
                            FROM FR_JEUNE J
                            LEFT OUTER JOIN FR_EQUIPE E ON J.EQUIPE_ID = E.ID
                            WHERE J.SEJOUR_ID = " . $sejourId . " ORDER BY PRENOM ASC;";
    $dbJeunesResult = query_mysql($mysqli, $selectJeunesQuery, "Impossible de trouver les jeunes du sejour '" . $sejourNom . "'");

    // Recupere les equipes de ce sejour. 
    $selectEquipesQuery = "SELECT CLE, NOM, COULEUR FROM FR_EQUIPE WHERE SEJOUR_ID = " . $sejourId . ";";
    $dbEquipesResult = query_mysql($mysqli, $selectEquipesQuery, "Impossible de trouver les equipes du sejour '" . $sejourNom . "'");

    $response->content = array("sejour" => $dbSejourResult[0], "animateurs" => $dbAnimsResult, "jeunes" => $dbJeunesResult, "equipes" => $dbEquipesResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>