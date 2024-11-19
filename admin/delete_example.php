<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK");

try {

    $mysqli = init_mysql_connection();

    $prefix = "EX_";
    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Supprime tous les jeunes avec le prefixe EX_. 
    $selectJeunesQuery = "SELECT ID FROM FR_JEUNE WHERE SEJOUR_ID = " . $sejourId . " AND NOM LIKE '" . $prefix . "%';";
    $dbJeunesResult = query_mysql($mysqli, $selectJeunesQuery, "Impossible de trouver les jeunes avec le prefixe '" . $prefix . "'.");
    for ($i = 0; $i < count($dbJeunesResult); $i++) {
        $jeuneId = $dbJeunesResult[$i]["ID"];
        // Supprime les defis valides par le jeune
        $deleteDefisQuery = "DELETE FROM FR_JEUNE_DEFI WHERE JEUNE_ID = " . $jeuneId . ";";
        query_mysql($mysqli, $deleteDefisQuery, "Impossible de supprimer les defis valides par le jeune avec l'id '" . $jeuneId . "'.");
        // Supprime le jeune
        $deleteJeuneQuery = "DELETE FROM FR_JEUNE WHERE ID = " . $jeuneId . ";";
        query_mysql($mysqli, $deleteJeuneQuery, "Impossible de supprimer le jeune avec l'id '" . $jeuneId . "'.");
    }

    // Supprime toutes les equipes avec le prefixe EX_. 
    $selectEquipesQuery = "SELECT ID FROM FR_EQUIPE WHERE SEJOUR_ID = " . $sejourId . " AND NOM LIKE '" . $prefix . "%';";
    $dbEquipesResult = query_mysql($mysqli, $selectEquipesQuery, "Impossible de trouver les equipes avec le prefixe '" . $prefix . "'.");
    for ($i = 0; $i < count($dbEquipesResult); $i++) {
        $equipeId = $dbEquipesResult[$i]["ID"];
        // Trouve s'il existe encore des jeunes dans cette equipe
        $selectJeuneEquipeQuery = "SELECT ID FROM FR_JEUNE WHERE SEJOUR_ID = " . $sejourId . " AND EQUIPE_ID = '" . $equipeId . "%';";
        $dbJeuneEquipeResult = query_mysql($mysqli, $selectJeuneEquipeQuery, "Impossible de trouver si des jeunes sont encore dans l'equipe avec l'id '" . $equipeId . "'.");
        if (count($dbJeuneEquipeResult) == 0) {
            // Supprime l'equipe
            $deleteEquipeQuery = "DELETE FROM FR_EQUIPE WHERE ID = " . $equipeId . ";";
            query_mysql($mysqli, $deleteEquipeQuery, "Impossible de supprimer l'equipe avec l'id '" . $equipeId . "'.");
        }
    }

    // Verifie que l'animateur existe et recupere son id.
    $selectAnimsQuery = "SELECT ID, NOM FROM FR_ANIMATEUR WHERE SEJOUR_ID = " . $sejourId . " AND NOM LIKE '" . $prefix . "%';";
    $dbAnimsResult = query_mysql($mysqli, $selectAnimsQuery, "Impossible de trouver les animateurs avec le prefixe '" . $prefix . "'.");
    for ($i = 0; $i < count($dbAnimsResult); $i++) {
        $animateurId = $dbAnimsResult[$i]["ID"];
        //Supprime les défis de l'animateur
        $deleteDefisQuery = "DELETE FROM FR_DEFI WHERE ANIMATEUR_ID = " . $animateurId . ";";
        query_mysql($mysqli, $deleteDefisQuery, "Impossible de supprimer l'animateur avec l'id '" . $animateurId . "'.");

        // Supprime l'animateur
        $deleteQuery = "DELETE FROM FR_ANIMATEUR WHERE ID = " . $animateurId . ";";
        query_mysql($mysqli, $deleteQuery, "Impossible de supprimer l'animateur avec l'id '" . $animateurId . "'.");
    }
    
    $response->message = "Les donnees d'exemple pour le sejour '" . $cleSejour . "' ont ete supprimees.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>