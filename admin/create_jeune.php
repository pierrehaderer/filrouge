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
    $prenom = clean_mysql_string($mysqli, $_POST['prenom']);
    $nom = clean_mysql_string($mysqli, $_POST['nom']);
    $cleEquipe = clean_mysql_string($mysqli, $_POST['equipe']);
    $age = clean_mysql_integer($mysqli, $_POST['age']);
    $cleJeune = genereCle($prenom, $nom);
    $photo = "";

    // Verifie que le sejour existe et recupere son id. 
    $selectSejourQuery = "SELECT ID FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];

    // Verifie que l'equipe existe et recupere son id.
    $selectEquipeQuery = "SELECT ID, NOM FROM FR_EQUIPE WHERE SEJOUR_ID = " . $sejourId . " AND CLE = '" . $cleEquipe . "';";
    $dbEquipeResult = query_mysql($mysqli, $selectEquipeQuery, "Impossible de trouver l'equipe avec la cle '" . $cleEquipe . "'.");
    $equipeId = (count($dbEquipeResult) > 0) ? $dbEquipeResult[0]["ID"] : "NULL";

    if (strlen($prenom) > 0) {
        // Recupere le fichier photo s'il est present
        if (strlen($_FILES["photo"]["name"]) > 0) {
            move_uploaded_file($_FILES["photo"]["tmp_name"], $photosPathTemp . $_FILES["photo"]["name"]);
            $photo = genPhotoName($cleSejour, $cleJeune);
            formatImage($photosPathTemp . $_FILES["photo"]["name"], $photosPath . $photo);
            unlink($photosPathTemp . $_FILES["photo"]["name"]);
        }

        // Insere le jeune en l'attachant au sejour
        $insertJeuneQuery = "INSERT INTO FR_JEUNE (CLE, NOM, PRENOM, PHOTO, AGE, EQUIPE_ID, SEJOUR_ID)
                               VALUES ('" . $cleJeune . "', '" . $nom . "', '" . $prenom . "', '" . $photo . "', " . $age . ", " . $equipeId . ", " . $sejourId . ");";
        query_mysql($mysqli, $insertJeuneQuery, "Impossible de creer le jeune '" . $nom . " " . $prenom . "'.");

        $response->message = "Le jeune '" . $prenom . " " . $nom . "' a ete cree.";
    }

    if (strlen($_FILES["csv"]["name"]) > 0) {
        $content = file_get_contents($_FILES['csv']['tmp_name']);
        $lines = explode("\n", $content);
        $message = "OK";
        foreach ($lines as $line) {
            if (!str_contains($line, "Prenom;Nom;Age") && strlen($line) > 3) {
                $jeune = explode(";", $line);
                $prenom = clean_mysql_string($mysqli, $jeune[0]);
                $nom = (count($jeune) > 1) ? clean_mysql_string($mysqli, $jeune[1]) : "";
                $age = (count($jeune) > 2) ? clean_mysql_integer($mysqli, $jeune[2]) : "0";
                $cleJeune = genereCle($prenom . "_" . $nom);
                try {
                    // Insere le jeune en l'attachant au sejour
                    $insertJeune2Query = "INSERT INTO FR_JEUNE (CLE, NOM, PRENOM, PHOTO, AGE, EQUIPE_ID, SEJOUR_ID)
                                            VALUES ('" . $cleJeune . "', '" . $nom . "', '" . $prenom . "', '" . $photo . "', " . $age . ", " . $equipeId . ", " . $sejourId . ");";
                    query_mysql($mysqli, $insertJeune2Query, "Impossible de creer le jeune '" . $nom . " " . $prenom . "'.");
                    $message = addHtmlMessage($message, "Le jeune '" . $nom . " " . $prenom . "' a ete cree.");
                } catch (Exception $e) {
                    $response->code = 100; // Warning
                    $message = addHtmlMessage($message, $e->getMessage());
                }
            }
        }
        $response->message = $message;
    }

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>