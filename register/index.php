<?php

include "../functions.php";

$comments = array();

function createNote($elem)
{
    global $comments;
    if (isset($comments[$elem], $comments[$elem]["type"], $comments[$elem]["message"])) {
        $text = $comments[$elem]["message"];
        $icon = getIcon($comments[$elem]["type"]);
        return "<span class='note'>
            <i class='$icon'></i>
            <span class='note-content'>$text</span>
        </span>";
    }
    return "";
}

function getElemType($elem)
{
    global $comments;
    if (isset($comments[$elem], $comments[$elem]["type"])) {
        return " " . $comments[$elem]["type"];
    }
    return "";
}

function getElemVal($elem)
{
    return (isset($_POST[$elem]) ? 'value="' . $_POST[$elem] . '"' : "");
}

if (isset($_POST["register"], $_POST["username"], $_POST["password1"], $_POST["password2"]) && (isset($_POST["email"]) || isset($_POST["lazy"]))) {
    $comments["username"] = validateUsername($_POST["username"]);
    $comments["password"] = array("type" => "error");
    if (!isset($_POST["lazy"])) {
        $comments["mail"] = validateEmailAddress($_POST["email"]);
    }

    if ($_POST["password1"] === $_POST["password2"]) {
        if (isset($_POST["lazy"]) || isPasswordValid($_POST["password1"])) {
            if (isset($_POST["lazy"]) && !isPasswordValid($_POST["password1"])) {
                $comments["password"]["type"] = "warning";
                $comments["password"]["message"] = "Although it is not enforced, we discourage the use of weak passwords.";
            } else {
                $comments["password"]["type"] = "success";
            }
        } else {
            $comments["password"]["message"] = "Passwords does not meet requirements.";
        }
    } else {
        $comments["password"]["message"] = "Passwords do not match.";
    }
    $success = true;
    foreach ($comments as $key => $val) {
        if (isset($val["type"]) && $val["type"] === 'error') {
            $success = false;
            break;
        }
    }

    if ($success === true) {
        if (register($_POST["username"], $_POST["password1"], (isset($_POST["lazy"]) ? null : $_POST["email"]))) {
            header("Location: http://sd4u.be/CityHunter/login?mode=register&type=success&strict=" . (isset($_POST['lazy']) ? "false" : "true"));
            die();
        } else {
            header("Location: http://sd4u.be/CityHunter/login?mode=register&type=error&strict=" . (isset($_POST['lazy']) ? "false" : "true"));
            die();
            //TODO maybe check for uniqueness again
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CityHunter - Register</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src='https://code.jquery.com/jquery-2.1.4.min.js'></script>
    <!--<script src='http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js'></script>-->

    <link rel="stylesheet" href="assets/css/styles.css"/>
    <script src="assets/js/jquery.complexify.js"></script>
    <script src="assets/js/script.js"></script>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
    <div id="main">
        <h1>Create an account!</h1>

        <noscript class="warning basic-padding">
            <i class="fa fa-warning"></i>
            Javascript seems to be turned off. Validate fields by submitting or allowing Javascript.
        </noscript>
        <form method="post" accept-charset="utf-8">
            <div class="input">
                <div class="input-row<?= getElemType("username") ?>">
                    <input type="text" id="username" name="username" placeholder="Username" required spellcheck="false" <?= getElemVal("username") ?>/>
                    <?= createNote("username") ?>
                </div>
                <div class="input-row<?= getElemType("mail") ?>" style="<?= (isset($_POST["lazy"])) ? "display: none;" : "" ?>">
                    <input type="email" id="email" name="email" placeholder="name@example.com" <?= (isset($_POST["lazy"])) ? "disabled" : "" ?> spellcheck="false" <?= getElemVal("email") ?>/>
                    <?= createNote("mail") ?>
                </div>
                <div class="input-row<?= getElemType("password") ?>">
                    <input type="password" id="password1" name="password1" placeholder="Password" required <?= getElemVal("password1") ?>/>
                    <?= createNote("password") ?>
                </div>
                <div class="input-row<?= getElemType("password") ?>">
                    <input type="password" id="password2" name="password2" placeholder="Password (repeat)" required <?= getElemVal("password2") ?>/>
                </div>
                <div class="input-row" id="lazy-row">
                    <div id="lazy-container-lazy-container" title="Turn off email and password strength requirements, your account will be deleted after 7 days.">
                        <span id="lazy-collapse" class="collapse-icon <?= (isset($_POST["lazy"])) ? "collapsed" : "" ?>" style="<?= (isset($_POST["lazy"])) ? "" : "display: none;" ?>" data-collapse-target="#lazy-note"></span>

                        <div id="lazy-container">
                            <input type="checkbox" id="lazy" name="lazy" <?= (isset($_POST["lazy"])) ? "checked" : "" ?> />
                            <label id="inside_bg" for="lazy"></label>
                        </div>
                        <label for="lazy" id="lazy-text">Fast sign up</label>
                    </div>
                    <div id="lazy-note" class="note info" style="<?= (isset($_POST["lazy"])) ? "" : "display: none;" ?>" data-collapse-source="#lazy-collapse">
                        <i class="fa fa-info-circle"></i>

                        <div class="note-content">You turned on <em>Fast sign up</em>, which means that you will NOT
                            need to:
                            <ul>
                                <li>Verify or submit an email address</li>
                                <li>Meet password strength standards</li>
                            </ul>
                            Your account will be deleted after 7 days if you do not activate it.
                        </div>
                    </div>
                </div>
                <img id="meter" alt="Password complexity meter" title="Password complexity" src="assets/img/meter.png"/>
                <img class="arrow" src="assets/img/arrow_only.png"/>
                <img class="arrowCap" src="assets/img/arrow_cap.png"/>

                <p class="meterText">Password Strength Meter</p>
            </div>
            <input type="submit" name="register" value="Register"/>
        </form>
    </div>
</body>
</html>
