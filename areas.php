<?php
require('core/Main.php');
$main = new Main();
$mainTemplate = $main->template;
$database = $main->db;
$functions = $main->functions;

$themeFile = "site.area";

$page = new TemplateHandler($themeFile);
$page->setVariable("siteName", Configuration::siteName);

$mainTemplate->setVariable("pageName", "Travel");
$mainTemplate->setVariable("content", $page->getTemplate());
$mainTemplate->setVariable("notifications", "");
$currentUser = $functions->getCurrentUser();

if (isset($_POST['Travel'])) {
  $id = trim($_POST['Travel']);
  $check = $database->executeQuery("SELECT * FROM areas WHERE id = '{$id}'");
  $price = $database->executeQuery("SELECT * FROM users WHERE Area = '{$id}'")->num_rows;
  $checkRows = $check->num_rows;
  $query = $check->fetch_array();
  if ($query['King'] == $currentUser['id']) {
    $cost = 0;
  } elseif($query['Type'] == 5){
    $cost = 0;
  } else {
    $cost = $price * 3;
  }
  if ($checkRows == 0) {
    $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Invalid Area.", Configuration::alertColours["warning"]));
  } elseif($currentUser['Area'] == $id){
    $mainTemplate->setVariable("notifications", $functions->getAlertHTML("You are already in this area.", Configuration::alertColours["warning"]));
  } elseif($cost > $currentUser['Gold']){
    $mainTemplate->setVariable("notifications", $functions->getAlertHTML("You're too poor to travel.", Configuration::alertColours["error"]));
  } else {
    $Gold = $currentUser['Gold'] - $cost;
    $database->executeQuery("UPDATE users SET Area ='{$id}', Gold = '{$Gold}' WHERE id='{$currentUser['id']}'");
    $database->executeQuery("UPDATE world_stats SET Value=Value + '{$cost}' WHERE id='1'");
    if ($query['King'] != 0) {
      $database->executeQuery("UPDATE users SET Gold = Gold + '{$cost}' WHERE id='{$query['King']}'");
    }
    $mainTemplate->setVariable("notifications", $functions->getAlertHTML("Congratulations! You're now in ".$query['Name'].".", Configuration::alertColours["success"]));
    // Reset Current User
    $currentUser = $functions->getCurrentUser();
  }
}



$lol = $database->executeQuery("SELECT * FROM areas WHERE Staff='0'");
$remaining = $lol->num_rows;
$ares = "";
while ($Q = $lol->fetch_array()) {
  $amount = $database->executeQuery("SELECT * FROM users WHERE Area = '{$Q['id']}'")->num_rows;
  $remaining--;
  /*if() {
      $ares .= '<div class="card-deck">';
  }*/
  $KingQ = $functions->getKing($Q['King']);
  if ($Q['Type'] == 1) {
    $City = '<a href="#" class="badge badge-warning">City</a>';
    $King = '<br><small class="text-dark">The current king of this area is: <strong>'.$KingQ.'</strong></small>';
    $Filter = "City";
  } elseif($Q['Type'] == 2){
    $City = '<a href="#" class="badge badge-danger">Dungeon</a>';
    $King = '';
    $Filter = "Dungeon";
  } elseif($Q['Type'] == 3){
    $City = '<a href="#" class="badge badge-info">Guild</a>';
    $King = '';
    $Filter = "Guild";
  } elseif($Q['Type'] == 4){
    $City = '<a href="#" class="badge badge-success">Event</a>';
    $King = '';
    $Filter = "Event";
  } else {
    $City = '';
    $King = '';
    $Filter = "Monster";
  }
  if ($currentUser['Area'] == $Q['id']) {
    $current = '<a href="#" class="badge badge-dark">Currently</a>';
  } else {
    $current = null;
  }
  if ($Q['King'] ==$currentUser['id']) {
    $cost = 0;
  } elseif($Q['Type'] == 5){
    $cost = 0;
  } else {
    $cost = $amount * 3;
  }
  if ($currentUser['Area'] == $Q['id']) {
    $button = '';
  } else {
    $button = '<p><button name="Travel" type="submit" value="'.$Q['id'].'" class="btn btn-primary btn-sm">Travel</button></p>';
  }
  $ares .= '<div class="col-md-4" id="area-'.$Filter.'">
  <div class="card">
    <img class="card-img-top" height="181.88" src="'.$Q['Image'].'" alt="'.$Q['Name'].'">
    <div class="card-body">
    <h5 class="card-title">'.$Q['Name'].' '.$City.' '.$current.'</h5>
      <p class="card-text">'.$Q['Description'].'</p>
      <p class="card-text"><small class="text-muted">There are currently '.$amount.' users in this area.</small>
      '.$King.'
      <small class="text-dark"><strong>Recommended Level:</strong></small><small> '.$Q['Level'].'</small>
      <br>
      <small class="text-danger"><strong>Costs per travel:</strong></small><small> '.$cost.' Gold</small></p>
      <form method="POST">
      '.$button.'
      </form>
    </div>
  </div></div>';
  /*if($index == 3) {
      $ares .= "</div>";
      $index = 0;
  }*/
}

$mainTemplate->setVariable("areas", $ares);
$mainTemplate->render();
