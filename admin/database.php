<?php

define('DB_NAME', 'TODO');
define('DB_USER', 'TODO');
define('DB_PASSWORD', 'TODO');
define('DB_HOST', 'TODO');

function init_mysql_connection() {
    return new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
}

function query_mysql($mysqli, $query, $errorMessage, $errorNoLine = false) {
    try {
        $result = $mysqli->query($query);
        if (!is_bool($result)) {
            $result = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (mysqli_sql_exception $e) {
        throw new Exception($errorMessage . " L'erreur est : " . $e->getMessage());
    }
    if ($errorNoLine && count($result) == 0) {
        throw new Exception($errorMessage);
    }
    return $result;
}

function close_mysql_connection($mysqli) {
    if ($mysqli != null) {
        $mysqli->close();
    }
}

function clean_mysql_string($mysqli, $param) {
    $param = str_replace(';', ',', $param);
    return mysqli_real_escape_string($mysqli, $param);
}

function clean_mysql_integer($mysqli, $param) {
    $param = preg_replace("/[^0-9]*/", '', $param);
    $param = mysqli_real_escape_string($mysqli, $param);
    return ($param == null || strlen($param) == 0) ? "0" : $param;
}

function createDatabaseIfNecessary($mysqli) {
  $request = "CREATE TABLE IF NOT EXISTS `FR_SEJOUR` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `CLE` VARCHAR(105) NOT NULL,
               `NOM` VARCHAR(100) NOT NULL,
               `METHODE` VARCHAR(20) NOT NULL DEFAULT 'AGE',
               `EQUIPE` tinyint(1) NOT NULL DEFAULT '0',
               `TABLEAU` VARCHAR(20) NOT NULL DEFAULT 'READY_PLAYER_ONE',
               `DATE_CREATION` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (`ID`),
               UNIQUE KEY `unique_fr_sejour_cle` (`CLE`)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");

  $request = "CREATE TABLE IF NOT EXISTS `FR_ANIMATEUR` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `CLE` VARCHAR(100) NOT NULL,
               `NOM` VARCHAR(100) NOT NULL,
               `SEJOUR_ID` int NOT NULL,
               PRIMARY KEY (`ID`),
               UNIQUE KEY `unique_nom_sejour_id` (`CLE`,`SEJOUR_ID`),
               KEY `sejour_id` (`SEJOUR_ID`),
               CONSTRAINT FOREIGN KEY (SEJOUR_ID) REFERENCES FR_SEJOUR (ID)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");

  $request = "CREATE TABLE IF NOT EXISTS `FR_DEFI` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `DESCRIPTION` VARCHAR(4000) NOT NULL,
               `ANIMATEUR_ID` INT NOT NULL,
               `TYPE` VARCHAR(20) DEFAULT NULL,
               `ACTIF` tinyint(1) NOT NULL DEFAULT '1',
               PRIMARY KEY (`ID`),
               KEY `animateur_id` (`ANIMATEUR_ID`),
               CONSTRAINT FOREIGN KEY (ANIMATEUR_ID) REFERENCES FR_ANIMATEUR (ID)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");

  $request = "CREATE TABLE IF NOT EXISTS `FR_EQUIPE` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `CLE` VARCHAR(100) NOT NULL,
               `NOM` VARCHAR(100) NOT NULL,
               `COULEUR` VARCHAR(20) NOT NULL,
               `SEJOUR_ID` INT NOT NULL,
               PRIMARY KEY (`ID`),
               KEY `sejour_id` (`SEJOUR_ID`),
               UNIQUE KEY `unique_equipe_cle_sejour_id` (`CLE`,`SEJOUR_ID`),
               CONSTRAINT FOREIGN KEY (SEJOUR_ID) REFERENCES FR_SEJOUR (ID)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");

  $request = "CREATE TABLE IF NOT EXISTS `FR_JEUNE` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `CLE` VARCHAR(200) NOT NULL,
               `PRENOM` VARCHAR(100) NOT NULL,
               `NOM` VARCHAR(100) NOT NULL,
               `PHOTO` VARCHAR(300) NOT NULL,
               `AGE` INT DEFAULT NULL,
               `EQUIPE_ID` INT DEFAULT NULL,
               `SEJOUR_ID` INT NOT NULL,
               PRIMARY KEY (`ID`),
               KEY `sejour_id` (`SEJOUR_ID`),
               KEY `equipe_id` (`EQUIPE_ID`),
               UNIQUE KEY `unique_jeune_cle_sejour_id` (`CLE`,`SEJOUR_ID`),
               CONSTRAINT FOREIGN KEY (SEJOUR_ID) REFERENCES FR_SEJOUR (ID),
               CONSTRAINT FOREIGN KEY (EQUIPE_ID) REFERENCES FR_EQUIPE (ID)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");

  $request = "CREATE TABLE IF NOT EXISTS `FR_JEUNE_DEFI` (
               `ID` INT NOT NULL AUTO_INCREMENT,
               `JEUNE_ID` INT NOT NULL,
               `DEFI_ID` INT NOT NULL,
               `POSITION` INT NOT NULL,
               `SCORE` INT NOT NULL,
               `DATE_VALIDATION` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (`ID`),
               KEY `jeune_id` (`JEUNE_ID`),
               KEY `defi_id` (`DEFI_ID`),
               CONSTRAINT FOREIGN KEY (DEFI_ID) REFERENCES FR_DEFI (ID),
               CONSTRAINT FOREIGN KEY (JEUNE_ID) REFERENCES FR_JEUNE (ID)
             ) ENGINE=InnoDB;";
  query_mysql($mysqli, $request, "Une erreur est surevenue lors de l'initialisation de la base.");
}

?>