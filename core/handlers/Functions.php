<?php

class Functions
{
    private $global;
    private $database;

    public function __construct($global)
    {
        $this->global = $global;
        if(Configuration::useDatabase) {
            $this->database = $this->global->db;
        }
    }

    public function urlClean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $string)); // Removes special chars.
    }

    // User Authentication
    public function encryptPassword($password, $user)
    {
        $finalString = $user . $password;
        $finalString = password_hash($finalString, PASSWORD_BCRYPT);
        return $finalString;
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function getAllRaces() {
        $races = array();

        $query = "SELECT * FROM races";
        $query = $this->database->executeQuery($query);

        while($row = $query->fetch_array()) {
            $index = sizeof($races);
            $races[$index]["RaceID"] = $row['id'];
            $races[$index]["Name"] = $row['RaceName'];
        }

        return $races;

    }

    public function getUserExpToLevel($level) {
        $a = Configuration::userLevelSettings["BaseXP"];
        $b = Configuration::userLevelSettings["XPMultiplier"];
        $c = Configuration::userLevelSettings["XPDivisor"];
        $d = Configuration::userLevelSettings["MultiplierInc"];

        $userLevel = (
            (
                ($a * $b)
                *
                ($level * $d)
            ) * $level
        ) / $c;

        return $userLevel;
    }
}
