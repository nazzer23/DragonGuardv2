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

if(isset($_POST['strUsername']) && isset($_POST['strPassword'])) {
    $username = $_POST['strUsername'];
    $rawPassword = $_POST['strPassword'];
    if(empty($username) || empty($rawPassword)) {

    } else {
        // Get Salt that is related to the user via the database
        $checkQuery = $database->executeQuery("SELECT Salt from users WHERE Username LIKE '{$username}'");
        if($checkQuery->num_rows <= 0) {
            // Account doesn't exist
        } else {
            $possibleUser = $checkQuery->fetch_array();            
            // Then check the password and the password in the database are the same
            $password = $functions->encryptPassword($rawPassword, $username, $possibleUser['Salt']);
            if($possibleUser['Hash'] == $password) {
                // login success
            } else {
                $possibleUser = null;
                // Login failed
            }
        }
    }
}

$mainTemplate->render();