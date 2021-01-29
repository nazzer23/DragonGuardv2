<?php
require('core/Main.php');
$main = new Main();
$mainTemplate = $main->template;
$database = $main->db;
$functions = $main->functions;

$themeFile = "site.create";

$page = new TemplateHandler($themeFile);
$page->setVariable("siteName", Configuration::siteName);

$mainTemplate->setVariable("pageName", "Sign Up");
$mainTemplate->setVariable("content", $page->getTemplate());
$mainTemplate->setVariable("notifications", "");

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// Race Options
$races = $functions->getAllRaces();

// Class Options
$classes = $functions->getStarterClasses();
$classesHTML = "";
foreach($classes as $class) {
    $classesHTML .= "<option class='dropdown-item' value='{$class["id"]}'>{$class["ClassName"]}</option>";
}
$mainTemplate->setVariable("classOptions", $classesHTML);

// Signup Functions
if (isset($_POST['Submit'])) {
    $username = trim($_POST['strUsername']);
    $password1 = trim($_POST['strPassword']);
    $cpassword = trim($_POST['strCPassword']);
    $password = $functions->encryptPassword($password1, $username);
    $email = trim($_POST['strEmail']);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $classID = trim($_POST['ClassID']);
    $raceID = trim($_POST['RaceID']);

    if (empty($username) || empty($password1) || empty($cpassword) || empty($email)) {
        $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Please enter a <b>username</b>, <b>password</b>, <b>confirm password</b> and <b>email</b>.", Configuration::alertColours["error"]));
    } elseif ($password1 != $cpassword) {
        $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Your passwords don't match.", Configuration::alertColours["warning"]));
    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Please enter a valid email address.", Configuration::alertColours["warning"]));
    } else {
        // Check to see if race value is valid
        $valid = false;
        for ($i = 0; $i < sizeof($races); $i++) {
            if ($raceID == $races[$i]["RaceID"]) {
                $valid = true;
            }
        }
        if (!$valid) {
            $mainTemplate->setVariable("notifications", $functions->getAlertHTML("The supplied RaceID is invalid.", Configuration::alertColours["error"]));
        } else {
            // Check to see if class value is valid
            $valid = false;
            for ($i = 0; $i < sizeof($classes); $i++) {
                if ($classID == $classes[$i]["ClassID"]) {
                    $valid = true;
                }
            }
            if (!$valid) {
                $mainTemplate->setVariable("notifications", $functions->getAlertHTML("The supplied ClassID is invalid.", Configuration::alertColours["error"]));
            } else {
                // Check to see if username is already in-use.
                $checkUserName = $database->executeQuery("SELECT * FROM users WHERE Name LIKE '{$username}'");
                if ($checkUserName->num_rows > 0) {
                    $mainTemplate->setVariable("notifications", $functions->getAlertHTML("The username you entered is already in-use.", Configuration::alertColours["error"]));
                } else {
                    $database->executeQuery("INSERT INTO users (`Name`, `Hash`, `Email`, `IP`, `RaceID`, `ClassID`) VALUES ('{$username}', '{$password}', '{$email}', '{$ip}', '{$raceID}', '{$classID}')");
                    $database->executeQuery("INSERT INTO users_items (`item_id`, `exp`, `equipped`, `quantity`) VALUES ('1', '0', '1', '1')");
                    $database->executeQuery("INSERT INTO users_items (`item_id`, `exp`, `equipped`, `quantity`) VALUES ('2', '0', '1', '1')");
                    $database->executeQuery("INSERT INTO users_items (`item_id`, `exp`, `equipped`, `quantity`) VALUES ('3', '0', '1', '1')");
                    $database->executeQuery("INSERT INTO users_items (`item_id`, `exp`, `equipped`, `quantity`) VALUES ('4', '0', '1', '1')");
                    if ($database->db->error) {
                        $mainTemplate->setVariable("notifications", $functions->getAlertHTML("There was an error whilst processing your request. Please contact the team.", Configuration::alertColours["error"]));
                    } else {
                        $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Your account has been successfully created.", Configuration::alertColours["success"]));
                    }
                }
            }
        }
    }

}

$mainTemplate->render();
