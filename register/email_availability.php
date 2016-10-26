<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-09-14
 * Time: 04:58
 */
$response = array();
if (isset($_POST["email"])) {
    include "../functions.php";
    $response = validateEmailAddress($_POST["email"]);
}
echo json_encode($response);
