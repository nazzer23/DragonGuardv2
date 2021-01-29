<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('core/Main.php');
$main = new Main();
$mainTemplate = $main->template;
$database = $main->db;
$functions = $main->functions;

$themeFile = "site.character";

$page = new TemplateHandler($themeFile);
$page->setVariable("siteName", Configuration::siteName);

$mainTemplate->setVariable("pageName", "Character's Page");
$mainTemplate->setVariable("content", $page->getTemplate());
$mainTemplate->setVariable("notifications", "");
$currentUser = $functions->getCurrentUser();
$mainTemplate->setVariable("Name", $currentUser['Name']);
$mainTemplate->setVariable("Avatar", $currentUser['Avatar']);
$Sword = $functions->getEquippedType($currentUser["id"], 'Sword');
$Helmet = $functions->getEquippedType($currentUser["id"], 'Helmet');
$Boots = $functions->getEquippedType($currentUser["id"], 'Boots');
$Armour = $functions->getEquippedType($currentUser["id"], 'Armour');
if(sizeof($Helmet) <= 0) {
    $fuck = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Helmet:</strong> Unequipped ';
} else {
    $fuck = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Helmet:</strong> '.$Helmet['Name'].' <button class="btn btn-warning btn-sm" type="submit" value="'.$Helmet['id'].'">Unequip</button><span class="badge badge-primary badge-pill">1</span></li>';
}

if(sizeof($Sword) <= 0) {
    $fuck1 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Sword:</strong> Unequipped ';
} else {
    $fuck1 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Sword:</strong> '.$Sword['Name'].' <button class="btn btn-warning btn-sm" type="submit" value="'.$Sword['id'].'">Unequip</button><span class="badge badge-primary badge-pill">1</span></li>';
}

if(sizeof($Boots) <= 0) {
    $fuck2 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Boots:</strong> Unequipped ';
} else {
    $fuck2 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Boots:</strong> '.$Boots['Name'].' <button class="btn btn-warning btn-sm" type="submit" value="'.$Boots['id'].'">Unequip</button><span class="badge badge-primary badge-pill">1</span></li>';
}

if(sizeof($Armour) <= 0) {
    $fuck3 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Armour:</strong> Unequipped</li>';
} else {
    $fuck3 = '<li class="list-group-item d-flex justify-content-between align-items-center"><strong>Armour:</strong> '.$Armour['Name'].' <button class="btn btn-warning btn-sm" type="submit" value="'.$Armour['id'].'">Unequip</button><span class="badge badge-primary badge-pill">1</span></li>';
}



$mainTemplate->setVariable("Helmet", $fuck);
$mainTemplate->setVariable("Sword", $fuck1);
$mainTemplate->setVariable("Boots", $fuck2);
$mainTemplate->setVariable("Armour", $fuck3);
$mainTemplate->render();
