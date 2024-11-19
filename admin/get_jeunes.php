<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $ordre = clean_mysql_string($mysqli, $_POST['ordre']);

    $sqlOrdre = ($ordre == "score") ? "SCORE DESC" : "PRENOM ASC";

    // Recupere les jeunes de ce sejour et leurs scores. 
    $selectScoreQuery = "SELECT J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, E.CLE AS EQUIPE, IFNULL(SUM(JD.SCORE), 0) AS SCORE
                           FROM FR_SEJOUR S
                             INNER JOIN FR_JEUNE J ON J.SEJOUR_ID = S.ID
                             LEFT OUTER JOIN FR_JEUNE_DEFI JD ON JD.JEUNE_ID = J.ID
                             LEFT OUTER JOIN FR_EQUIPE E ON E.ID = J.EQUIPE_ID
                           WHERE S.CLE = '" . $cleSejour . "'
                           GROUP BY J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, E.CLE
                           ORDER BY " . $sqlOrdre;
    $dbScoreResult = query_mysql($mysqli, $selectScoreQuery, "Impossible de trouver les jeunes du sejour avec la cle '" . $cleSejour . "'.");

    $response->content = array("jeunes" => $dbScoreResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>