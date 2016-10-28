<?php

$response = array();
if (isset($_POST["email"])) {
    include "../functions.php";
    $response = validateEmailAddress($_POST["email"]);
}
echo json_encode($response);
