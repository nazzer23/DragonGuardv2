<?php
require('core/Main.php');
$main = new Main();
$mainTemplate = $main->template;
$database = $main->db;
$functions = $main->functions;

$themeFile = "site.login";

$page = new TemplateHandler($themeFile);
$page->setVariable("siteName", Configuration::siteName);

$mainTemplate->setVariable("pageName", "Login");
$mainTemplate->setVariable("content", $page->getTemplate());
$mainTemplate->setVariable("loginNotification", $functions->getAlertHTML("Please enter your username and password.", Configuration::alertColours["info"]));

if(isset($_POST['strUsername']) && isset($_POST['strPassword'])) {
    $username = $_POST['strUsername'];
    $rawPassword = $_POST['strPassword'];
    if(empty($username) || empty($rawPassword)) {
        // This will tell the user that nothing has been entered.
        $mainTemplate->setVariable("loginNotification", $functions->getAlertHTML("Please enter your username and password.", Configuration::alertColours["info"]));
    } else {
        // Get Salt that is related to the user via the database
        $checkQuery = $database->executeQuery("SELECT * from users WHERE Name LIKE '{$username}'");
        if($checkQuery->num_rows <= 0) {
            // Account doesn't exist
            $mainTemplate->setVariable("loginNotification", $functions->getAlertHTML("The username you entered doesn't exist.", Configuration::alertColours["warning"]));
        } else {
            $possibleUser = $checkQuery->fetch_array();            
            // Then check the password and the password in the database are the same
            if($functions->verifyPassword($username, $rawPassword, $possibleUser['Hash'])) {
                // Assign the user data to a variable
                $_SESSION['userLogged'] = true;
                $_SESSION['userID'] = $possibleUser['id'];
                header("Location: " . $main->getWorkingDirectory());
            } else {
                $possibleUser = null;
                $mainTemplate->setVariable("loginNotification", $functions->getAlertHTML("The username and password combination you entered was invalid.", Configuration::alertColours["error"]));
            }
        }
    }
}

$mainTemplate->render();