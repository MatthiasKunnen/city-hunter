<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-02-03
 * Time: 09:07
 */
include_once 'psl-config-cityhunter.php';   // As functions.php is not included
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->set_charset("utf8");