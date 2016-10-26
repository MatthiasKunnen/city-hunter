<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-09-27
 * Time: 19:33
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
require "classes/notification.php";
use CityHunter\Notification;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CityHunter - Home</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src='https://code.jquery.com/jquery-2.1.4.min.js'></script>
    <link rel="stylesheet" href="css/main.css"/>
    <script src="js/main.js"></script>
</head>
<body>
    <ul class="notifications">
        <?php
        $notifications = array(
            //Direct calls available >= PHP 5.4
            /*
            //new Notification("These are not the droid you're looking for!")->isError(),
            //new Notification("Your account will expire in x days, x hours and x minutes.")->isWarning(),
            new Notification("
                Who are you?<br>
                Who? Who is but the form following the function of what and what I am is a man in a mask.<br>
                Well I can see that.<br>
                Of course you can. I'm not questioning your powers of observation I'm merely remarking upon the paradox of asking a masked man who he is.<br>
            ")->isSuccess()
            */
        );
        foreach ($notifications as $val) {
            echo $val;
        }

        ?>
    </ul>
    <div id="main">
        <nav>
            <div class="nav-item">
                <div class="nav-option" data-collapse="collapsed">
                    <i class="fa fa-fw fa-cog"></i>
                    Profile
                    <i class="nav-option-button fa fa-angle-up"></i>
                </div>
                <div class="nav-content" style="display: none">
                    <ul id="profile-properties">
                        <li>

                        </li>
                    </ul>
                    <form action="change-password.php" method="post">
                        <label>

                            <input type="password" name="old_password">
                        </label>
                        <input type="password" name="new_pass1">
                        <input type="password" name="new_pass2">
                    </form>
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-option" data-collapse="visible">
                    <i class="fa fa-fw fa-gamepad"></i>
                    Game
                    <i class="nav-option-button fa fa-angle-down"></i>
                </div>
                <div class="nav-content" data-collapse="visible">
                    <table class="table game-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Created on</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fa fa-balance-scale"></i></td>
                                <td>
                                    <time>2015-02-14 20:00</time>
                                </td>
                                <td>Lobby</td>
                                <td>Join</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-gamepad"></i></td>
                                <td>
                                    <time>2015-05-18 18:47</time>
                                </td>
                                <td>Finished</td>
                                <td>Join</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="nav-item">
                <div class="nav-option" data-collapse="collapsed">
                    <i class="fa fa-fw fa-info-circle"></i>
                    About
                    <i class="nav-option-button fa fa-angle-up"></i>
                </div>
                <div class="nav-content" style="display: none">
                    <h2>Dit project is gerealiseerd door Evert De Vos en <a href="mailto:matthias.kunnen@gmail.com">Matthias Kunnen</a></h2>
                </div>
            </div>
        </nav>
    </div>
</body>
</html>
