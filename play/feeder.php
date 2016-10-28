<?php

require "functions.php";
session_start();
if (!isset($_SESSION["id"])) {
    header("location: ./");
} else {
    switch ($_GET["mode"]) {
        case "update":
            if (isset($_GET["latitude"], $_GET["longitude"], $_GET["accuracy"], $_GET["timestamp"])) {
                newLocation($_SESSION["id"], $_GET["latitude"], $_GET["longitude"], $_GET["accuracy"], $_GET["timestamp"]);
            }
            break;
        case "request":
            $update = getUpdate($_SESSION["id"]);
            echo json_encode($update);
            unset($update["countdown"]);
            setcookie("prey_position", json_encode($update));
            break;
        case "refresh":
            echo json_encode(getRefreshRate($_SESSION["game_id"]));
            break;
    }
}
