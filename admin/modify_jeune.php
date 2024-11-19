<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

try {

    $mysqli = init_mysql_connection();
    $photosPath = "../photos/";
    $photosPathTemp = "../photos/temp/";
    if (!file_exists($photosPathTemp)) {
        mkdir($photosPathTemp);
    }

    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);
    $cleJeune = clean_mysql_string($mysqli, $_POST['cle']);
    $newPrenom = clean_mysql_string($mysqli, $_POST['prenom']);
    $newNom = clean_mysql_string($mysqli, $_POST['nom']);
    $newAge = clean_mysql_integer($mysqli, $_POST['age']);
    $newCleEquipe = clean_mysql_string($mysqli, $_POST['equipe']);
    $newPhoto = "";
    $newCle = genereCle($newNom, $newPrenom);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que le jeune existe et recupere son id. 
    $selectJeuneQuery = "SELECT ID, NOM, PRENOM, PHOTO FROM FR_JEUNE WHERE CLE = '" . $cleJeune . "' AND SEJOUR_ID = " . $sejourId . ";";
    $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver le jeune avec la cle '" . $cleJeune . "'.", true);
    $jeuneId = $dbJeuneResult[0]["ID"];
    $jeuneNom = $dbJeuneResult[0]["NOM"];
    $jeunePrenom = $dbJeuneResult[0]["PRENOM"];
    $photo = $dbJeuneResult[0]["PHOTO"];

    // Verifie que l'equipe existe et recupere son id.
    $selectEquipeQuery = "SELECT ID, NOM FROM FR_EQUIPE WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $newCleEquipe . "';";
    $dbEquipeResult = query_mysql($mysqli, $selectEquipeQuery, "Impossible de trouver l'equipe avec la cle '" . $newCleEquipe . "'.");
    $equipeId = (count($dbEquipeResult) > 0) ? $dbEquipeResult[0]["ID"] : "NULL";

    // Recupere la photo si elle est presente
    if (strlen($_FILES["photo"]["name"]) > 0) {
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photosPathTemp . $_FILES["photo"]["name"]);
        $newPhoto = genPhotoName($cleSejour, $cleJeune);
        formatImage($photosPathTemp . $_FILES["photo"]["name"], $photosPath . $newPhoto);
        unlink($photosPathTemp . $_FILES["photo"]["name"]);
    }

   // Deplace la photo si elle n'a pas ete fournie, si elle etait presente et si la cle a change
    if (strlen($newPhoto) == 0 && $cleJeune != $newCle && strlen($photo) > 0) {
      $newPhoto = genPhotoName($cleSejour, $newCle);
      rename($photosPath.$photo, $photosPath.$newPhoto);
    }

    // Modifie la photo si elle a ete fournie ou elle a ete deplacee
    if (strlen($newPhoto) > 0) {
        $updatePhotoQuery = "UPDATE FR_JEUNE SET PHOTO = '" . $newPhoto . "' WHERE ID = " . $jeuneId . ";";
        query_mysql($mysqli, $updatePhotoQuery, "Impossible de modifier la photo du jeune '" . $jeuneNom . " " . $jeunePrenom . "'.");
    }

    // Modifie le jeune
    $updateQuery = "UPDATE FR_JEUNE SET CLE = '" . $newCle . "', NOM = '" . $newNom . "', PRENOM = '" . $newPrenom . "', AGE = " . $newAge . ", EQUIPE_ID = " . $equipeId . " WHERE ID = " . $jeuneId . ";";
    query_mysql($mysqli, $updateQuery, "Impossible de modifier le jeune '" . $jeuneNom . " " . $jeunePrenom . "'.");

    $response->message = "Le jeune '" . $jeuneNom . " " . $jeunePrenom . "' a ete modifie.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>