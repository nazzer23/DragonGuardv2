<?php

class DatabaseHandler
{
    public $db;

    public function __construct()
    {
        $dbCreds = Configuration::getDatabase();
        $this->db = new mysqli($dbCreds["dbHost"], $dbCreds["dbUser"], $dbCreds["dbPass"], $dbCreds["dbName"]);
        if ($this->db->connect_error) {
            if(Configuration::devMode) {
                die($this->db->connect_error);
            } else {
                die("There was an error whilst connecting to the database. Please contact the site administrator.");
            }
        }
    }

    public function fetchArray($query)
    {
        return $this->executeQuery($query)->fetch_array();
    }

    public function executeQuery($query)
    {
        $query = $this->db->query($query);
        if($this->db->error) {
            if(Configuration::devMode) {
                die($this->db->error);
            } else {
                die("An error has occured. Please inform staff. Error ID: {$this->db->errno}");
            }
        }
        return $query;
    }

    public function fetchObject($query)
    {
        return $this->executeQuery($query)->fetch_object();
    }

    public function getNumberOfRows($query)
    {
        return $this->executeQuery($query)->num_rows;
    }

    public function escapeArray($array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->escapeString($value);
        }
        return $array;
    }

    public function escapeString($value)
    {
        return $this->db->real_escape_string($value);
    }
}

?>