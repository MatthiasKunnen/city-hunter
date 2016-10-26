<?php
if (isset($_GET["action"]) && $_GET["action"] == "logout") {
    session_start();
    session_unset();
}

include "../functions.php";

$message = array("class" => array(), "icon" => getIcon("info"), "text" => array());
if (isset($_GET["mode"], $_GET["type"], $_GET["strict"])) {
    switch ($_GET["mode"]) {
        case "register":
            $type = $_GET["type"];
            switch ($type) {
                case 'success':
                    array_push($message["class"], "info");
                    array_push($message["text"], "Your account has been created.");
                    if ($_GET["strict"] === 'true') {
                        array_push($message["text"], "Don't forget to activate your account by clicking on the link we send you by email.");
                    }
                    break;
                case 'error':
                    array_push($message["class"], "error");
                    $message["icon"]=getIcon('error');
                    array_push($message["text"], "Oops, something went wrong on our end. We are not able to confirm the creation of your account and will look into this issue.");
                    break;
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CityHunter - Log in</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-card">
        <h1>Log In</h1><br>
        <?php
        if (count($message["class"]) > 0 && count($message["text"]) > 0) {
            ?>
            <span id="login-message" class="<?= implode(" ", $message["class"]) ?>">
                        <i class="<?= $message["icon"] ?>"></i>
                <?= implode(" ", $message["text"]) ?>
                    </span>
            <?php
        }
        ?>
        <form method="post" action="../">
            <input type="text" name="username" placeholder="Username">
            <input type="password" name="password" placeholder="Password">
            <input type="submit" name="login" class="login login-submit" value="login">
        </form>
        <div id="register-container">
            <a href="../register/" title="Register">Register</a>
        </div>
    </div>
</body>
</html>
