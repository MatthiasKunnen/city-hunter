<?php

$response = array();
if (isset($_POST["username"])) {
    include "../functions.php";
    $response = validateUsername($_POST["username"]);
}
echo json_encode($response);
