<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-09-13
 * Time: 04:18
 */
$response = array();
if (isset($_POST["username"])) {
    include "../functions.php";
    $response = validateUsername($_POST["username"]);
}
echo json_encode($response);
