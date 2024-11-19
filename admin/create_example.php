<?php

include("database.php");
include("utils.php");

$response = (object) array("code" => "0", "message" => "OK"); 

$animateurs = array("EX_lucio" ,"EX_mimi" ,"EX_kirby" ,"EX_calvin", "EX_fatalis", "EX_raf", "EX_nazir", "EX_topi", "EX_zulgard");
$equipes = array("EX_Coquelicot" ,"EX_Pissenlit" ,"EX_Rose" ,"EX_Petunia");
$equipesCouleur = array("BLUE" ,"RED" ,"CYAN" ,"GREEN");
$jeunesPrenom = array("Adryen", "Alexis", "Alexis", "Anthony", "Arthur", "Augustin", "Cael", "Cantin", "Come", "Damien", "Dany", "Edouard", "Edouard", "Emile", "Ethan", "Ethanael", "Felix", "Frank", "Gabriel", "Gabriel", "Germain", "Issa", "Iwan", "Leandre", "Leon", "Leon", "Lucie", "Luke", "Mael", "Marco", "Marius", "Martin", "Martin", "Mathis", "Maxime", "Nael", "Nathan", "Noa", "Oscar", "Paul", "Rafik", "Raphael", "Salome", "Silamaka", "Thibault", "Ylann");
$jeunesNom = array("EX_SASSI", "EX_CAMA", "EX_MARTINE", "EX_SAN", "EX_LUC", "EX_BELLANG", "EX_BRILL", "EX_DID", "EX_MERLI", "EX_CORL", "EX_GENDR", "EX_PAPUCH", "EX_LEMENNA", "EX_REMILI", "EX_JI", "EX_DEMBROS", "EX_LEYMAR", "EX_SAINT-ROMA", "EX_BENAIMEC", "EX_FIGA", "EX_CONSTANCI", "EX_JI", "EX_FOUCA", "EX_LED", "EX_ROUG", "EX_STOU", "EX_DREUILL", "EX_KO", "EX_JOUAN-BLONDEA", "EX_JOURD", "EX_GROMFE", "EX_RU", "EX_GIANOL", "EX_LISOV", "EX_TROL", "EX_DEFOR", "EX_ROSSIGN", "EX_BER", "EX_BOY", "EX_PECHE", "EX_MARTINE", "EX_DAV", "EX_BOY", "EX_HILZHEB", "EX_BL", "EX_TOUCHA");
$jeunesAge = array("8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11", "12", "13", "14", "8", "9", "10", "11");
$defis = array();
array_push($defis, "EX_calvin#M'impressionner avec une figure difficile au diabolo");
array_push($defis, "EX_calvin#Vaincre mon armée de petits bonshommes en noir");
array_push($defis, "EX_calvin#Dessine moi un mouton");
array_push($defis, "EX_calvin#Épeler CALVIN a quelqu'un qui est sourd");
array_push($defis, "EX_fatalis#It's dangerous to go alone ! Take this ! (Me fournir une épée sous n'importe quelle forme (dessin, Pixel Art ...))");
array_push($defis, "EX_fatalis#Who's That Pokémon ? (Dessiner une silhouette de Pokémon et me la faire deviner)");
array_push($defis, "EX_fatalis#Red is sus ! (Organiser un 'emergency meeting' et accuser celui en rouge)");
array_push($defis, "EX_zulgard#Victoire royale");
array_push($defis, "EX_zulgard#Kirby Victory");
array_push($defis, "EX_zulgard#Bonus : Kerbal Space Program");
array_push($defis, "EX_kirby#Nage droit devant toi");
array_push($defis, "EX_kirby#Hakuna Matata#");
array_push($defis, "EX_kirby#chanter la reine des neiges");
array_push($defis, "EX_lucio#Expelliarmus !");
array_push($defis, "EX_lucio#UN CREEPER !!!");
array_push($defis, "EX_lucio#Que la force soit avec toi");
array_push($defis, "EX_topi#Kage bushin");
array_push($defis, "EX_topi#PLATINE C'est l'heure du du du duel");
array_push($defis, "EX_mimi#nba");
array_push($defis, "EX_mimi#la bouteille");
array_push($defis, "EX_mimi#libérée délivrée");
array_push($defis, "EX_raf#4 tortue ninja");
array_push($defis, "EX_raf#345 pi");
array_push($defis, "EX_raf#Mime le monstre 23");
array_push($defis, "EX_nazir#Gâté");
array_push($defis, "EX_nazir#C'est pas la capitale ...");

$equipesId = array();
$jeunesId = array();
$animateursId = array();
$defisId = array();

try {

    $mysqli = init_mysql_connection();
    $photosPath = "../photos/";
    $photosExPath = "../photos/site/";
    
    $cleSejour = clean_mysql_string($mysqli, $_POST['sejour']);

    // Verifie que le sejour existe et recupere son id.
    $selectSejourQuery = "SELECT ID, NOM, METHODE FROM FR_SEJOUR WHERE CLE = '" . $cleSejour . "';";
    $dbSejourResult = query_mysql($mysqli, $selectSejourQuery, "Impossible de trouver le sejour avec la cle '" . $cleSejour . "'.", true);
    $sejourId = $dbSejourResult[0]["ID"];
    $sejourNom = $dbSejourResult[0]["NOM"];
    $sejourMethode = $dbSejourResult[0]["METHODE"];

    // Insere les equipes et garde leurs ids pour plus tard
    for ($i = 0; $i < count($equipes); $i++) {
        $cle = genereCle($equipes[$i]);
        $nom = $equipes[$i];
        $couleur = $equipesCouleur[$i];
        $selectEquipeQuery = "SELECT ID FROM FR_EQUIPE WHERE CLE = '" . $cle . "' AND SEJOUR_ID = '" . $sejourId . "';";
        $dbEquipeResult = query_mysql($mysqli, $selectEquipeQuery, "Impossible de retrouver l'equipe '" . $cle . "'.");
        if (count($dbEquipeResult) > 0) {
            $equipesId[$i] = $dbEquipeResult[0]["ID"];
        } else {
            $insertQuery = "INSERT INTO FR_EQUIPE (CLE, NOM, COULEUR, SEJOUR_ID) VALUES ('" . $cle . "', '" . $nom . "', '" . $couleur . "', " . $sejourId . ");";
            query_mysql($mysqli, $insertQuery, "Impossible de creer l'equipe '" . $nom . "'.");
            $selectEquipe2Query = "SELECT ID FROM FR_EQUIPE WHERE CLE = '" . $cle . "' AND SEJOUR_ID = '" . $sejourId . "';";
            $dbEquipe2Result = query_mysql($mysqli, $selectEquipe2Query, "Impossible de retrouver l'equipe '" . $cle . "'.");
            $equipesId[$i] = $dbEquipe2Result[0]["ID"];
        }
    }

    // Insere des jeunes et garde leurs ids pour plus tard
    for ($i = 0; $i < count($jeunesPrenom); $i++) {
        $prenom = $jeunesPrenom[$i];
        $nom = $jeunesNom[$i];
        $age = $jeunesAge[$i];
        $equipe = $equipesId[$i % 4];
        $cle = genereCle($prenom, $nom);
        if ($i < 10) {
            $photo = genPhotoName($cleSejour, $cle);
            copy($photosExPath . $i . ".jpg", $photosPath . $photo);
        } else {
            $photo = "";
        }
        $insertJeuneQuery = "INSERT INTO FR_JEUNE (CLE, NOM, PRENOM, PHOTO, AGE, EQUIPE_ID, SEJOUR_ID) VALUES ('" . $cle . "', '" . $nom . "', '" . $prenom . "', '" . $photo . "', " . $age . ", " . $equipe . ", " . $sejourId . ");";
        query_mysql($mysqli, $insertJeuneQuery, "Impossible de creer le jeune '" . $nom . " " . $prenom . "'.");
        $selectJeuneQuery = "SELECT ID, NOM, PRENOM, PHOTO FROM FR_JEUNE WHERE CLE = '" . $cle . "' AND SEJOUR_ID = " . $sejourId . ";";
        $dbJeuneResult = query_mysql($mysqli, $selectJeuneQuery, "Impossible de trouver le jeune avec la cle '" . $cle . "'.", true);
        $jeunesId[$i] = $dbJeuneResult[0]["ID"];
    }

    // Insere les animateurs et garde leurs ids pour plus tard
    for ($i = 0; $i < count($animateurs); $i++) {
        $cle = genereCle($animateurs[$i]);
        $nom = $animateurs[$i];
        $insertAnimExQuery = "INSERT INTO FR_ANIMATEUR (CLE, NOM, SEJOUR_ID) VALUES ('" . $cle . "', '" . $nom . "', " . $sejourId . ");";
        query_mysql($mysqli, $insertAnimExQuery, "Impossible de creer l'animateur '" . $nom . "'.");
        $selectAnimExQuery = "SELECT ID FROM FR_ANIMATEUR WHERE CLE = '" . $cle . "' AND SEJOUR_ID = '" . $sejourId . "';";
        $dbAnimExResult = query_mysql($mysqli, $selectAnimExQuery, "Impossible de retrouver l'animateur '" . $nom . "'.", true);
        $animateursId[$animateurs[$i]] = $dbAnimExResult[0]["ID"];
    }

    // Insere les defis et garde leurs ids pour plus tard
    for ($i = 0; $i < count($defis); $i++) {
        $animateur = explode("#", $defis[$i])[0];
        $description = clean_mysql_string($mysqli, explode("#", $defis[$i])[1]);
        $insertDefiExQuery = "INSERT INTO FR_DEFI (DESCRIPTION, TYPE, ANIMATEUR_ID) VALUES ('" . $description . "', 'DECREASING', " . $animateursId[$animateur] . ");";
        query_mysql($mysqli, $insertDefiExQuery, "Impossible de creer le defi '" . getSummary($description) . "'.");
        $selectDefiExQuery = "SELECT ID FROM FR_DEFI WHERE ANIMATEUR_ID = " . $animateursId[$animateur] . " ORDER BY ID DESC LIMIT 1;";
        $dbDefiExResult = query_mysql($mysqli, $selectDefiExQuery, "Impossible de retrouver le defi avec la description '" . getSummary($description) . "'.", true);
        $defisId[$i] = $dbDefiExResult[0]["ID"];
    }

    // Valide des defis pour les jeunes
    $date = new DateTime();
    for ($i = 0; $i < count($defisId); $i++) {
        for ($j = 0; $j <= $i; $j++) {
            $date->modify('-5 second');
            $position = $i - $j + 1;
            $score = getScore($sejourMethode, 'DECREASING', $jeunesAge[$j], $position);
            $insertDefiValideQuery = "INSERT INTO FR_JEUNE_DEFI (JEUNE_ID, DEFI_ID, SCORE, POSITION, DATE_VALIDATION) " .
                                        " VALUES ('" . $jeunesId[$j] . "', '" . $defisId[$i] . "', " . $score . ", " . $position . ", '" . date_format($date, 'Y-m-d H:i:s') . "');";
            query_mysql($mysqli, $insertDefiValideQuery, "Impossible de valider le defi avec l'id '" .  $defisId[$i] . "' pour le jeune avec l'id '" .  $jeunesId[$j] . "'.");
        }
    }

    $response->message = "Les donnees d'exemple ont ete creees pour le sejour '" . $sejourNom . "'.";

} catch (Exception $e) {
    $response = (object) array("code" => "1", "message" => $e->getMessage());
}

close_mysql_connection($mysqli);
echo(json_encode($response));
?>