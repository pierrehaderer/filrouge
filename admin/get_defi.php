<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $defiId = clean_mysql_string($mysqli, $_POST['defi']);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que le defi existe et recupere son animateur et sa description.
    $selectDefiQuery = "SELECT ANIMATEUR_ID, DESCRIPTION FROM FR_DEFI WHERE ID = " . $defiId . ";";
    $dbDefiRresult = query_mysql($mysqli, $selectDefiQuery, "Impossible de trouver le defi avec l'id '" . $defiId . "'.", true);
    $animateurId = $dbDefiRresult[0]["ANIMATEUR_ID"];
    $description = $dbDefiRresult[0]["DESCRIPTION"];

    // Verifie que l'animateur existe et fait partie de ce séjour.
    $selectAnimQuery = "SELECT ID FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND ID = " . $animateurId . ";";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec l'id '" . $animateurId . "' de ce defi.", true);

    // Recupere les jeunes qui ont valide ce defi. 
    $selectJeunesQuery = "SELECT J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, E.CLE AS EQUIPE, IFNULL(MIN(JD.POSITION), '-') AS POSITION,
                                 IFNULL(SUM(JD.SCORE), '-') AS SCORE, IFNULL(DATE_FORMAT(MAX(JD.DATE_VALIDATION), '%d %b %H:%i:%s'), '-') AS DATE_VALIDATION
                           FROM FR_SEJOUR S, FR_JEUNE J
                           LEFT OUTER JOIN FR_JEUNE_DEFI JD ON JD.JEUNE_ID = J.ID AND JD.DEFI_ID = " . $defiId . "
                           LEFT OUTER JOIN FR_EQUIPE E ON E.ID = J.EQUIPE_ID
                           WHERE S.ID = " . $sejourId . "
                             AND J.SEJOUR_ID = S.ID
                           GROUP BY J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, E.CLE
                           ORDER BY J.PRENOM ASC";
    $dbJeunesResult = query_mysql($mysqli, $selectJeunesQuery, "Impossible de trouver les jeunes qui ont valide le defi '" . getSummary($description) . "'.");

    $response->content = array("jeunes" => $dbJeunesResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>