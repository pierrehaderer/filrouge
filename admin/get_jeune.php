<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleJeune = clean_mysql_string($mysqli, $_POST['jeune']);

    // Recupere les infos du jeune. 
    $selectJeuneQuery = "SELECT J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE, IFNULL(SUM(JD.SCORE), 0) AS SCORE
                           FROM FR_SEJOUR S, FR_JEUNE J
                             LEFT OUTER JOIN FR_JEUNE_DEFI JD ON JD.JEUNE_ID = J.ID
                           WHERE S.CLE = '" . $cleSejour . "'
                             AND J.SEJOUR_ID = S.ID
                             AND J.CLE = '" . $cleJeune . "'
                           GROUP BY J.CLE, J.PRENOM, J.NOM, J.PHOTO, J.AGE";
    $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver les infos du jeune avec la cle '" . $cleJeune . "'.", true);

    // Recupere les defis du jeune. 
    $selectDefisQuery = "SELECT JD.ID AS JEUNE_DEFI_ID, D.DESCRIPTION, JD.SCORE, POSITION, DATE_FORMAT(JD.DATE_VALIDATION, '%d %b %H:%i:%s') AS DATE_VALIDATION
                           FROM FR_SEJOUR S, FR_JEUNE J, FR_JEUNE_DEFI JD, FR_DEFI D
                           WHERE S.CLE = '" . $cleSejour . "'
                             AND J.SEJOUR_ID = S.ID
                             AND J.CLE = '" . $cleJeune . "'
                             AND JD.JEUNE_ID = J.ID
                             AND D.ID = JD.DEFI_ID
                             ORDER BY JD.DATE_VALIDATION ASC";
    $dbDefisResult = query_mysql($mysqli, $selectDefisQuery, "Impossible de trouver les defis du jeune avec la cle '" . $cleJeune . "'.");

    $response->content = array("jeune" => $dbJeuneResult[0], "defis" => $dbDefisResult);

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>