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

// Race Options
$races = $functions->getAllRaces();
print_r($races);

// Signup Functions
if(isset($_POST['strUsername']) && isset($_POST['strPassword']) && isset($_POST['strEmail']) && isset($_POST['ClassID']) && isset($_POST['RaceID'])) {

}

$mainTemplate->render();