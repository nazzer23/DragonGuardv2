<?php
require('core/Main.php');
$main = new Main();
$mainTemplate = $main->template;
$database = $main->db;
$functions = $main->functions;

$themeFile = "site.home";

$page = new TemplateHandler($themeFile);
$page->setVariable("siteName", Configuration::siteName);

$mainTemplate->setVariable("pageName", "Home");
$mainTemplate->setVariable("content", $page->getTemplate());

$currentUser = $functions->getCurrentUser();

$mainTemplate->render();