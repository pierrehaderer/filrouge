<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleJeune = clean_mysql_string($mysqli, $_POST['jeune']);
    $jeuneDefiId = clean_mysql_string($mysqli, $_POST['jeune_defi']);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que le defi existe et recupere son animateur et sa description.
    $selectDefiQuery = "SELECT D.ANIMATEUR_ID, D.DESCRIPTION FROM FR_DEFI D, FR_JEUNE_DEFI JD WHERE D.ID = JD.DEFI_ID AND JD.ID = " . $jeuneDefiId . ";";
    $dbDefiRresult = query_mysql($mysqli, $selectDefiQuery, "Impossible de trouver le defi du defi valide dont l'id est '" . $jeuneDefiId . "'.", true);
    $animateurId = $dbDefiRresult[0]["ANIMATEUR_ID"];
    $description = $dbDefiRresult[0]["DESCRIPTION"];

    // Verifie que l'animateur existe et fait partie de ce séjour.
    $selectAnimQuery = "SELECT ID FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND ID = " . $animateurId . ";";
    $dbAnimResult = query_mysql($mysqli, $selectAnimQuery, "Impossible de trouver l'animateur avec l'id '" . $animateurId . "' de ce defi.", true);

    // Verifie que le jeune existe et recupere son id. 
    $selectJeuneQuery = "SELECT ID, NOM, PRENOM, PHOTO FROM FR_JEUNE WHERE CLE = '" . $cleJeune . "' AND SEJOUR_ID = " . $sejourId . ";";
    $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver le jeune avec la cle '" . $cleJeune . "'.", true);
    $jeuneId = $dbJeuneResult[0]["ID"];
    $jeuneNom = $dbJeuneResult[0]["NOM"];
    $jeunePrenom = $dbJeuneResult[0]["PRENOM"];

    // Supprime la validation de ce defi pour le jeune
    $deleteQuery = "DELETE FROM FR_JEUNE_DEFI WHERE JEUNE_ID = " . $jeuneId . " AND ID = " . $jeuneDefiId . ";";
    query_mysql($mysqli, $deleteQuery, "Impossible de supprimer la validation du defi dont l'id est '" . $jeuneDefiId . "'.");

    $response->message = "La validation du defi '" . getSummary($description) . "' a ete supprime pour '" . $jeunePrenom . " " . $jeuneNom . "'.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>