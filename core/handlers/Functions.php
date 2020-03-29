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
    public function encryptPassword($password, $user, $salt)
    {
        $length = (strlen($password.$user) + $salt) / 5;
        $finalString = $password;
        for ($i = 0; $i < $length; $i++) {
            $finalString = base64_encode($finalString);
        }
        return $finalString;
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

    public function getExpToLevel($level) {
        return ;
    }
}
