<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleAnimateur = clean_mysql_string($mysqli, $_POST['animateur']);
    $defiId = clean_mysql_string($mysqli, $_POST['defi']);
    $cleJeunes = explode("#", clean_mysql_string($mysqli, $_POST['jeunes']));

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID, METHODE FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];
    $sejourMethode = $dbSejourResult[0]["METHODE"];

    // Verifie que l'animateur existe et recupere son id.
    $selectAnimQuery = "SELECT ID, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleAnimateur . "';";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec la cle '" . $cleAnimateur . "'.", true);
    $animateurId = $dbAnimResult[0]["ID"];

    // Verifie que le defi existe et recupere sa description et son type.
    $selectDefiQuery = "SELECT ID, TYPE, DESCRIPTION FROM FR_DEFI WHERE ID = " . $defiId . " AND ANIMATEUR_ID = " . $animateurId . ";";
    $dbDefiRresult = query_mysql($mysqli, $selectDefiQuery, "Impossible de trouver le defi avec l'id '" . $defiId . "'.", true);
    $description = $dbDefiRresult[0]["DESCRIPTION"];
    $defiType = $dbDefiRresult[0]["TYPE"];

    // Determine le rang auquel les jeunes ont valide le defi. 
    $selectPositionQuery = "SELECT IFNULL(MAX(POSITION) + 1, 1) AS NEXT_POSITION FROM FR_JEUNE_DEFI WHERE DEFI_ID = " . $defiId . ";";
    $dbPositionResult = query_mysql($mysqli, $selectPositionQuery, "Impossible de trouver le rang pour ce defi.", true);
    $nextPosition = $dbPositionResult[0]["NEXT_POSITION"];

    foreach ($cleJeunes as $cleJeune) {
        // Verifie que le jeune existe et recupere son id. 
        $selectJeuneQuery = "SELECT ID, NOM, PRENOM, AGE FROM FR_JEUNE WHERE CLE = '" . $cleJeune . "' AND SEJOUR_ID = " . $sejourId . ";";
        $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver le jeune avec la cle '" . $cleJeune . "'.", true);
        $jeuneId = $dbJeuneResult[0]["ID"];
        $jeuneNom = $dbJeuneResult[0]["NOM"];
        $jeunePrenom = $dbJeuneResult[0]["PRENOM"];
        $jeuneAge = $dbJeuneResult[0]["AGE"];

        // Verifie si le jeune n'a pas deja valide le defi.
        if ($defiType != "REUSABLE") {
            $selectDejaValideQuery = "SELECT COUNT(*) AS DEFI_VALIDE FROM FR_JEUNE_DEFI WHERE JEUNE_ID = " . $jeuneId . " AND DEFI_ID = " . $defiId . ";";
            $dbDejaValideResult = query_mysql($mysqli, $selectDejaValideQuery, "Impossible de trouver si le jeune a deja valide le defi.", true);
            if (intval($dbDejaValideResult[0]["DEFI_VALIDE"]) > 0) {
                $response->message = addHtmlMessage($response->message, "Ce defi a deja ete valide par '" . $jeunePrenom . " " . $jeuneNom . "'.");
                continue;
            }
        }

        // Calculer le score
        $score = getScore($sejourMethode, $defiType, $jeuneAge, $nextPosition);

        // Valide le defi pour le jeune
        $insertQuery = "INSERT INTO FR_JEUNE_DEFI (JEUNE_ID, DEFI_ID, SCORE, POSITION) VALUES (" . $jeuneId . ", " . $defiId . ", " . $score . ", " . $nextPosition . ");";
        query_mysql($mysqli, $insertQuery, "Impossible de valider le defi.");

        $response->message = addHtmlMessage($response->message, "Le defi '" . getSummary($description) . "' a ete valide pour '" . $jeunePrenom . " " . $jeuneNom . "'.");
    }
} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>