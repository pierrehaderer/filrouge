<?php

include("HeicToJpg.php");

$decreasingDefiValues = array(200, 160, 130, 110, 100, 90, 80, 70, 60, 50);
$constantDefiValue = 200;

function returnErrorAndExit($response, $code, $message) {
    $response->code = $code;
	$response->message = $message;
    echo(json_encode($response));
    exit();
}

function genereCle($param, $param2 = null) {
    $cle = $param;
    if ($param2 != null) {
        $cle .= "_" . $param2;
    }
    $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', ' ' => "_" );
    $cle = strtr( $cle, $unwanted_array );
    $cle = preg_replace("/[^a-zA-Z0-9_]*/", '', $cle);
    $cle = strtolower($cle);
    return $cle;
}

function genereCleSejour($param) {
    $cle = genereCle($param);
    // Ajout 4 digits aleatoire apres le param
    $random = rand() % 10000;
    $time = time() % 10000;
    return $cle . "_" . sprintf("%04s", ($random + $time) % 10000);
}

function getSummary($param) {
    return (strlen($param) <= 20) ? $param : substr($param, 0, 20) . " ...";
}

function addHtmlMessage($message, $ajout) {
    if ($message == "OK") {
        return $ajout;
    } else {
        return $message . "<br>" . $ajout;
    }
}

function genPhotoName($sejour, $cle) {
    return $sejour . "_" . $cle . "_" . time() . ".jpg";
}

function formatImage($imageSource, $imageDest) {
    $extension = substr($imageSource, strrpos($imageSource, "."));
    if ($extension == ".jpg") {
        list($width, $height) = getimagesize($imageSource);
        $percent = ($width > 300) ? 300 / $width : 1;
        $newwidth = floor($width * $percent);
        $newheight = floor($height * $percent);
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        $source = imagecreatefromjpeg($imageSource);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagejpeg($thumb, $imageDest);
    } else if ($extension == ".png") {
        list($width, $height) = getimagesize($imageSource);
        $percent = ($width > 300) ? 300 / $width : 1;
        $newwidth = floor($width * $percent);
        $newheight = floor($height * $percent);
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        $source = imagecreatefrompng($imageSource);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagejpeg($thumb, $imageDest);
//    } else if ($extension == ".heic") {
//        HeicToJpg::convert($imageSource)->saveAs($imageDest);
    } else {
        throw new Exception("Ce format de photo n'est pas accepte. Utiliser 'jpeg' ou 'png'");
    }
}
/**
 * Le calcul du score utilise un coefficient et une valeur
 * Selon la methode de calcul du sejour, un 1er coefficient depend de l'age ou est constant
 * Selon le type de defi, un 2nd coefficient est applique, pour un defi de type 'SUPER', ce coefficient vaut 5
 * Selon le type de defi, la valeur du defi est soit une constante, soit elle diminue en fonction du nombre de joueur qui ont deja valide le defi
 * Ensuite, on multiplie les coefficients et cette valeur
 */
function getScore($methode, $defiType, $age, $nextRank) {
    global $decreasingDefiValues, $constantDefiValue;
    $coefficientAge = 10;
    if ($methode == "AGE" && intval($age) > 0) {
        $coefficientAge = max(21 - intval($age), 4); 
    }
    $coefficientSuper = 1;
    if ($defiType == "SUPER_CONSTANT" || $defiType == "SUPER_DECREASING") {
        $coefficientSuper = 5;
    }
    $value = $constantDefiValue;
    if ($defiType == "DECREASING" || $defiType == "SUPER_DECREASING") {
        $decreasingRank = min(intval($nextRank), count($decreasingDefiValues)) - 1;
        $value = $decreasingDefiValues[$decreasingRank];
    }
    return $coefficientAge * $coefficientSuper * $value;
}

?>