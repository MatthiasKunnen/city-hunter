<?php
require "functions.php";
session_start();
if (isset($_SESSION["id"]) || isset($_POST["login"], $_POST["game"], $_POST["username"], $_POST["password"])) {
    if (isset($_SESSION["id"]) || login($_POST["game"], $_POST["username"], $_POST["password"])) {
        if (!isset($_SESSION["id"])) {
            $_SESSION["id"] = getPlayerID($_POST["username"]);
            $_SESSION["game"] = $_POST["game"];
            $_SESSION["game_id"] = getGameID($_POST["game"]);
            $_SESSION["username"] = $_POST["username"];
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>map</title>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <link rel="stylesheet" href="../css/site.css">
            <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
            <script src="https://maps.googleapis.com/maps/api/js"></script>
            <script src="../js/hunter.js"></script>
        </head>
        <body>
            <div id="overMap">
                <div>
                    <span>Logged in as <?= $_SESSION["username"] ?></span>
                    <a href="../login?action=logout">Log out</a>
                </div>
                <div style="text-align: center">
                    Next update: <span id="time"></span>
                </div>
            </div>
            <div id="map-canvas"></div>
        </body>
        </html>
        <?php
    } else {
        header("location: ./login?login=failed");
    }
} else {
    header("location: ./login");
}
?>