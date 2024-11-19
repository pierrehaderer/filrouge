<?php

include("admin/database.php");
include("admin/utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $ordre = clean_mysql_string($mysqli, $_POST['ordre']);

    $sqlOrdre = ($ordre == "score") ? "" : "PRENOM ASC";

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID, CLE, NOM, METHODE, EQUIPE, TABLEAU FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];
    $sejourNom = $dbSejourResult[0]["NOM"];

    // Recupere les equipes de ce sejour. 
    $selectEquipesQuery = "SELECT E.CLE, E.NOM, E.COULEUR, IFNULL(SUM(JD.SCORE), 0) AS SCORE
                             FROM FR_EQUIPE E
                               LEFT OUTER JOIN FR_JEUNE J ON J.EQUIPE_ID = E.ID
                               LEFT OUTER JOIN FR_JEUNE_DEFI JD ON JD.JEUNE_ID = J.ID
                             WHERE E.SEJOUR_ID = " . $sejourId . "
                             GROUP BY E.CLE, E.NOM, E.COULEUR
                             ORDER BY SCORE DESC;";
    $dbEquipesResult = query_mysql($mysqli, $selectEquipesQuery, "Impossible de trouver les equipes du sejour '" . $sejourNom . "'");

    // Recupere les jeunes de ce sejour et leurs scores. 
    $selectJeunesQuery = "SELECT J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, IFNULL(E.NOM, '') AS EQUIPE, IFNULL(SUM(JD.SCORE), 0) AS SCORE
                           FROM FR_JEUNE J
                             LEFT OUTER JOIN FR_JEUNE_DEFI JD ON JD.JEUNE_ID = J.ID
                             LEFT OUTER JOIN FR_EQUIPE E ON E.ID = J.EQUIPE_ID
                           WHERE J.SEJOUR_ID = '" . $sejourId . "'
                           GROUP BY J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, EQUIPE
                           ORDER BY SCORE DESC";
    $dbJeunesResult = query_mysql($mysqli, $selectJeunesQuery, "Impossible de trouver les jeunes du sejour '" . $sejourNom . "'.");

    $response->content = array("sejour" => $dbSejourResult[0], "equipes" => $dbEquipesResult, "jeunes" => $dbJeunesResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>