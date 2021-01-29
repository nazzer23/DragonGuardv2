<?php
// Start of the log out file
require("core/Main.php");
$main = new Main(false);

unset($_SESSION["userLogged"]);
unset($_SESSION["userID"]);

session_destroy();

header("Location: {$main->getWorkingDirectory()}");