<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-05-14
 * Time: 17:24
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
require "includes/db_connect_cityhunter.php";

function updateLastRefresh($game_id)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("UPDATE game SET last_refresh = unix_timestamp() WHERE id=?")) {
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
    }
}

function getRefreshRate($game_id)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT game_refresh_rate, location_refresh_rate, last_refresh + game_refresh_rate - unix_timestamp() FROM game WHERE id=?")) {
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($game, $loc, $diff);
        $stmt->fetch();
        if ($diff < 0) {
            updateLastRefresh($game_id);
        }
        return array(
            "game_refresh_rate" => $game,
            "location_refresh_rate" => $loc,
            "countdown" => $diff
        );
    } else {
        return array(
            "game_refresh_rate" => 15 * 60,
            "location_refresh_rate" => 5
        );
    }
}

function getGameID($game_name)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT id FROM game WHERE password=?")) {
        $stmt->bind_param("s", $game_name);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    }
    return -1;
}

function getLastRefresh($game_id)
{
    global $mysqli;
    $refreshtime = "No last refresh found.";
    if ($stmt = $mysqli->prepare("SELECT refreshrate FROM game WHERE id=?")) {
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($refreshtime);
        $stmt->fetch();
    }
    return $refreshtime;
}

function getUpdate($my_id)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT g.last_refresh + g.game_refresh_rate - unix_timestamp(),  c.latitude, c.longitude FROM player p
  INNER JOIN game g ON p.game_id = g.id
  INNER JOIN player pt ON p.search_player_id = pt.id
  INNER JOIN coordinate c ON c.player_id = pt.id
  WHERE p.id=?
  order by c.retrieved desc
  LIMIT 1")
    ) {
        $stmt->bind_param("i", $my_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($time, $latitude, $longitude);
        $stmt->fetch();
        return array(
            "countdown" => $time,
            "latitude" => $latitude,
            "longitude" => $longitude
        );
    }
    return null;
}

function getPlayerID($username)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT p.id FROM player p WHERE p.username=?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id);
        $stmt->fetch();
        return $id;
    } else {
        return $mysqli->error;
    }
}

function newLocation($my_id, $latitude, $longitude, $accuracy, $timestamp)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("INSERT INTO coordinate (latitude, longitude, accuracy, retrieved, player_id) VALUES (?, ?, ?, ?, ?)")) {
        $stmt->bind_param("ddiii", $latitude, $longitude, $accuracy, $timestamp, $my_id);
        $stmt->execute();
        echo $stmt->error;
    } else {
        echo $mysqli->error;
    }
}

function login($game, $username, $password)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT p.password, p.salt FROM player p
INNER JOIN game g ON p.game_id = g.id
WHERE p.username=? AND g.password=?")
    ) {
        $stmt->bind_param("ss", $username, $game);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hash, $salt);
        $stmt->fetch();
        if ($hash && $salt) {
            return hash("sha256", $password . $salt) === $hash;
        }
    } else {
        echo $mysqli->error . "<br>";
    }
    return false;
}

function register($username, $password, $opt_email = null)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("INSERT INTO cityhunter.user (username, email, password, salt, date_created, activation_code, is_fast_signup) VALUES (?, ?, ?, ?, NOW(), ?, ?)")) {
        $hash = generateHash($password);
        $activationCode = isset($opt_email) ? hash("sha256", $username . getNonce()) : null;
        $is_fast_sign_up = !(isset($opt_email) && $opt_email !== null);
        $stmt->bind_param("sssssi", $username, $opt_email, $hash["hash"], $hash["salt"], $activationCode, $is_fast_sign_up);
        $stmt->execute();
        return !($stmt->errno > 0);
    } else {
        return false;
    }
}


function getNonce($length = 22)
{
    return base64_encode(openssl_random_pseudo_bytes($length));
}

function generateHash($data = "", $saltLength = 22)
{
    $salt = getNonce($saltLength);
    return array(
        "hash" => hash("sha256", $data . $salt),
        "salt" => $salt
    );
}

function isUsernameAvailable($username)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT u.id FROM user u WHERE u.username=?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows === 0;
    } else {
        return false;
    }
}

function isEmailAddressAvailable($email)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT u.id FROM user u WHERE u.email=?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows === 0;
    } else {
        return false;
    }
}

function validateUsername($username)
{
    $response = array("type" => "error");
    if (preg_match('/^[A-Za-z][A-Za-z0-9_]{2,20}$/', $username)) {
        if (isUsernameAvailable($username)) {
            $response["type"] = "success";
            $response["message"] = "Username is available";
        } else {
            $response["message"] = "Username is not available";
        }
    } else {
        if (strlen($username) < 3) {
            $response["message"] = "Username is too short! (min 3)";
        } else if (strlen($username) > 20) {
            $response["message"] = "Username is too long enough! (max 20)";
        } else if (substr($username, 0, 1) === '_') {
            $response["message"] = "Username cannot start with underscore!";
        } else {
            $response["message"] = "Only alphanumeric characters and underscore allowed.";
        }
    }

    return $response;
}

function validateEmailAddress($email)
{
    $response = array("type" => "error");
    if (preg_match('/^\S+?@+?\S+?$/', $email)) {
        if (strlen($email) > 254) {
            $response["message"] = "Email address is too long.";
        } elseif (isEmailAddressAvailable($email)) {
            $response["type"] = "success";
            $response["message"] = "Email address is valid and available.";
        } else {
            $response["message"] = "Email address is already in use.";
        }
    } else {
        $response["message"] = "We couldn't recognize an email address in this enquiry.";
    }
    return $response;
}

function isPasswordValid($password)
{
    $MIN_COMPLEXITY = 49; // 12 chars with Upper, Lower and Number
    $MAX_COMPLEXITY = 120; //  25 chars, all charsets
    $CHARSETS = array(
        // Commonly Used
        ////////////////////
        array(0x0030, 0x0039), // Numbers
        array(0x0041, 0x005A), // Uppercase
        array(0x0061, 0x007A), // Lowercase
        array(0x0021, 0x002F), // Punctuation
        array(0x003A, 0x0040), // Punctuation
        array(0x005B, 0x0060), // Punctuation
        array(0x007B, 0x007E), // Punctuation
        // Everything Else
        ////////////////////
        array(0x0080, 0x00FF), // Latin-1 Supplement
        array(0x0100, 0x017F), // Latin Extended-A
        array(0x0180, 0x024F), // Latin Extended-B
        array(0x0250, 0x02AF), // IPA Extensions
        array(0x02B0, 0x02FF), // Spacing Modifier Letters
        array(0x0300, 0x036F), // Combining Diacritical Marks
        array(0x0370, 0x03FF), // Greek
        array(0x0400, 0x04FF), // Cyrillic
        array(0x0530, 0x058F), // Armenian
        array(0x0590, 0x05FF), // Hebrew
        array(0x0600, 0x06FF), // Arabic
        array(0x0700, 0x074F), // Syriac
        array(0x0780, 0x07BF), // Thaana
        array(0x0900, 0x097F), // Devanagari
        array(0x0980, 0x09FF), // Bengali
        array(0x0A00, 0x0A7F), // Gurmukhi
        array(0x0A80, 0x0AFF), // Gujarati
        array(0x0B00, 0x0B7F), // Oriya
        array(0x0B80, 0x0BFF), // Tamil
        array(0x0C00, 0x0C7F), // Telugu
        array(0x0C80, 0x0CFF), // Kannada
        array(0x0D00, 0x0D7F), // Malayalam
        array(0x0D80, 0x0DFF), // Sinhala
        array(0x0E00, 0x0E7F), // Thai
        array(0x0E80, 0x0EFF), // Lao
        array(0x0F00, 0x0FFF), // Tibetan
        array(0x1000, 0x109F), // Myanmar
        array(0x10A0, 0x10FF), // Georgian
        array(0x1100, 0x11FF), // Hangul Jamo
        array(0x1200, 0x137F), // Ethiopic
        array(0x13A0, 0x13FF), // Cherokee
        array(0x1400, 0x167F), // Unified Canadian Aboriginal Syllabics
        array(0x1680, 0x169F), // Ogham
        array(0x16A0, 0x16FF), // Runic
        array(0x1780, 0x17FF), // Khmer
        array(0x1800, 0x18AF), // Mongolian
        array(0x1E00, 0x1EFF), // Latin Extended Additional
        array(0x1F00, 0x1FFF), // Greek Extended
        array(0x2000, 0x206F), // General Punctuation
        array(0x2070, 0x209F), // Superscripts and Subscripts
        array(0x20A0, 0x20CF), // Currency Symbols
        array(0x20D0, 0x20FF), // Combining Marks for Symbols
        array(0x2100, 0x214F), // Letterlike Symbols
        array(0x2150, 0x218F), // Number Forms
        array(0x2190, 0x21FF), // Arrows
        array(0x2200, 0x22FF), // Mathematical Operators
        array(0x2300, 0x23FF), // Miscellaneous Technical
        array(0x2400, 0x243F), // Control Pictures
        array(0x2440, 0x245F), // Optical Character Recognition
        array(0x2460, 0x24FF), // Enclosed Alphanumerics
        array(0x2500, 0x257F), // Box Drawing
        array(0x2580, 0x259F), // Block Elements
        array(0x25A0, 0x25FF), // Geometric Shapes
        array(0x2600, 0x26FF), // Miscellaneous Symbols
        array(0x2700, 0x27BF), // Dingbats
        array(0x2800, 0x28FF), // Braille Patterns
        array(0x2E80, 0x2EFF), // CJK Radicals Supplement
        array(0x2F00, 0x2FDF), // Kangxi Radicals
        array(0x2FF0, 0x2FFF), // Ideographic Description Characters
        array(0x3000, 0x303F), // CJK Symbols and Punctuation
        array(0x3040, 0x309F), // Hiragana
        array(0x30A0, 0x30FF), // Katakana
        array(0x3100, 0x312F), // Bopomofo
        array(0x3130, 0x318F), // Hangul Compatibility Jamo
        array(0x3190, 0x319F), // Kanbun
        array(0x31A0, 0x31BF), // Bopomofo Extended
        array(0x3200, 0x32FF), // Enclosed CJK Letters and Months
        array(0x3300, 0x33FF), // CJK Compatibility
        array(0x3400, 0x4DB5), // CJK Unified Ideographs Extension A
        array(0x4E00, 0x9FFF), // CJK Unified Ideographs
        array(0xA000, 0xA48F), // Yi Syllables
        array(0xA490, 0xA4CF), // Yi Radicals
        array(0xAC00, 0xD7A3), // Hangul Syllables
        array(0xD800, 0xDB7F), // High Surrogates
        array(0xDB80, 0xDBFF), // High Private Use Surrogates
        array(0xDC00, 0xDFFF), // Low Surrogates
        array(0xE000, 0xF8FF), // Private Use
        array(0xF900, 0xFAFF), // CJK Compatibility Ideographs
        array(0xFB00, 0xFB4F), // Alphabetic Presentation Forms
        array(0xFB50, 0xFDFF), // Arabic Presentation Forms-A
        array(0xFE20, 0xFE2F), // Combining Half Marks
        array(0xFE30, 0xFE4F), // CJK Compatibility Forms
        array(0xFE50, 0xFE6F), // Small Form Variants
        array(0xFE70, 0xFEFE), // Arabic Presentation Forms-B
        array(0xFEFF, 0xFEFF), // Specials
        array(0xFF00, 0xFFEF), // Halfwidth and Fullwidth Forms
        array(0xFFF0, 0xFFFD)  // Specials
    );
    $complexity = 0;
    for ($i = count($CHARSETS) - 1; $i >= 0; $i--) {
        $charset = $CHARSETS[$i];
        for ($a = strlen($password) - 1; $a >= 0; $a--) {
            $charCode = utf8CharToHex(mb_substr($a, 0, 1, "UTF-8"));
            if ($charset[0] <= $charCode && $charCode <= $charset[1]) {
                $complexity += $charset[1] - $charset[0] + 1;
            }
        }
    }
    // Use natural log to produce linear scale
    $complexity = log(pow($complexity, strlen($password))) * (1 / 0.7);
    return ($complexity > $MIN_COMPLEXITY && strlen($password) >= 6);
}

function utf8CharToHex($char)
{
    $convmap = array(0x0, 0xffff, 0, 0xffff);
    $decimal = substr(mb_encode_numericentity($char, $convmap, "UTF-8"), -5, 4);
    return "0x" . base_convert($decimal, 10, 16);
}

function getIcon($type)
{
    $icons = array(
        "error" => "fa fa-times-circle",
        "warning" => "fa fa-warning",
        "success" => "fa fa-check",
        "info" => "fa fa-info-circle"
    );
    return isset($icons[$type]) ? $icons[$type] : "";
}