<?php

class Functions
{
    private $global;
    private $database;

    public function __construct($global)
    {
        $this->global = $global;
        if (Configuration::useDatabase) {
            $this->database = $this->global->db;
        }
    }

    #region Site Functionality
    public function urlClean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $string)); // Removes special chars.
    }

    public function getAlertHTML($message, $type)
    {
        $alertTemplate = new TemplateHandler("components/component.alertmessage");
        $alertTemplate->setVariable("alertName", $type);
        $alertTemplate->setVariable("alertMessage", $message);
        return $alertTemplate->getTemplate();
    }
    #endregion

    #region User Related Functions
    public function encryptPassword($password, $user)
    {
        $finalString = strtolower($user) . $password;
        $finalString = password_hash($finalString, PASSWORD_DEFAULT);
        return $finalString;
    }


    public function verifyUserLoggedIn()
    {
        if (isset($_SESSION['userLogged'])) {
            return $_SESSION['userLogged'];
        } else {
            return false;
        }
    }

    public function verifyPassword($user, $password, $hash)
    {
        return password_verify(strtolower($user) . $password, $hash);
    }

    public function getUserData($userID)
    {
        $query = "SELECT * FROM users WHERE id = {$userID}";
        $userQuery = $this->database->executeQuery($query);
        if ($userQuery->num_rows <= 0) {
            return [];
        } else {
            return $userQuery->fetch_array();
        }
    }


    public function getCurrentUser()
    {
        return $this->getUserData($_SESSION['userID']);
    }

    public function getUserExpToLevel($level)
    {
        $a = Configuration::userLevelSettings["BaseXP"];
        $b = Configuration::userLevelSettings["XPMultiplier"];
        $c = Configuration::userLevelSettings["XPDivisor"];
        $d = Configuration::userLevelSettings["MultiplierInc"];

        return round((
                (
                    ($a * $b)
                    *
                    ($level * $d)
                ) * $level
            ) / $c);
    }

    public function canUserLevelUp($level, $userData)
    {
        if ($this->getUserExpToLevel($level) <= $userData["EXP"]) {
            if ($level === Configuration::gameSettings["MaxLevel"]) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function calculateMaxUserHP($level)
    {
        $hp = 100;
        // TODO: Some actual calculations
        return round($hp);
    }
    #endregion

    #region Race Data
    public function getAllRaces()
    {
        $races = array();

        $query = "SELECT * FROM races";
        $query = $this->database->executeQuery($query);

        while ($row = $query->fetch_array()) {
            $index = sizeof($races);
            $races[$index]["RaceID"] = $row['id'];
            $races[$index]["RaceName"] = $row['RaceName'];
        }

        return $races;
    }

    public function getRaceByID($raceID)
    {
        $query = "SELECT * FROM races WHERE id = {$raceID}";
        $raceQuery = $this->database->executeQuery($query);
        if ($raceQuery->num_rows <= 0) {
            return [];
        } else {
            return $raceQuery->fetch_array();
        }
    }
    #endregion

    #region Class Data
    public function getAllClasses()
    {
        $classes = array();

        $query = "SELECT * FROM classes";
        $query = $this->database->executeQuery($query);

        while ($row = $query->fetch_array()) {
            $index = sizeof($classes);
            $classes[$index]["id"] = $row['id'];
            $classes[$index]["ClassName"] = $row['ClassName'];
            $classes[$index]["Starter"] = $row["Starter"];
        }

        return $classes;
    }

    public function getStarterClasses()
    {
        $classes = array();

        $query = "SELECT * FROM classes WHERE Starter = 1";
        $query = $this->database->executeQuery($query);

        while ($row = $query->fetch_array()) {
            $index = sizeof($classes);
            $classes[$index]["id"] = $row['id'];
            $classes[$index]["ClassName"] = $row['ClassName'];
        }

        return $classes;
    }

    public function getClassByID($classID)
    {
        $query = "SELECT * FROM classes WHERE id = {$classID}";
        $classQuery = $this->database->executeQuery($query);
        if ($classQuery->num_rows <= 0) {
            return [];
        } else {
            return $classQuery->fetch_array();
        }
    }

    public function getUserSkills($userID)
    {
        $query = "SELECT * FROM users_skills WHERE userID = {$userID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_all();
    }

    public function getSkillData($skillID)
    {
        $query = "SELECT * FROM skills WHERE id = {$skillID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query;
    }
    #endregion

    #region Item Data
    public function getItems()
    {
        $items = array();

        $query = "SELECT * FROM items";
        $query = $this->database->executeQuery($query);

        while ($row = $query->fetch_array()) {
            $index = sizeof($items);
            $items[$index]["id"] = $row["id"];
            $items[$index]["Name"] = $row["Name"];
            $items[$index]["Description"] = $row["Description"];
        }

        return $items;
    }

    public function getItemByID($itemID)
    {

    }
    #endregion

    #region User Item Data
    public function getUserItem($userID, $itemID)
    {
        $query = "SELECT * FROM users_items WHERE itemID={$itemID} AND userID={$userID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_array();
    }

    public function getUserItems($userID)
    {
        $query = "SELECT * FROM users_items WHERE userID={$userID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_array();
    }

    public function getEquippedType($userID, $Type)
    {
        if ($Type == "Helmet") {
            $Type = 2;
        } elseif ($Type == "Armour") {
            $Type = 1;
        } elseif ($Type == "Sword") {
            $Type = 0;
        } elseif ($Type == "Boots") {
            $Type = 3;
        }
        $fuck = $this->database->executeQuery("SELECT items.*, users_items.userID, users_items.equipped FROM users_items INNER JOIN items ON users_items.itemID = items.id WHERE users_items.userID = '{$userID}' AND equipped='1' AND Type = '{$Type}'");
        if ($fuck->num_rows == 0) {
            return [];
        } else {
            return $fuck->fetch_array();
        }
    }

    public function getEquippedUserItems($userID)
    {
        $query = "SELECT * FROM users_items WHERE userID={$userID} AND equipped=1";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_array();
    }

    public function getUserEquippedWeapon($userID)
    {
        $query = "SELECT * FROM users_items INNER JOIN items ON users_items.itemID = items.id WHERE userID={$userID} AND items.Type = 0 AND equipped=1";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_array();
    }
    #endregion

    #region Stat System
    public function getUserStats($userID)
    {
        $userData = $this->getUserData($userID);
        if (sizeof($userData) <= 0) {
            return [];
        }
        $stats = array(
            "STR" => $userData["Strength"],
            "DEF" => $userData["Defence"],
            "INT" => $userData["Intelligence"],
            "SPD" => $userData["Speed"]
        );
        return $stats;
    }

    public function getUserItemStats($userID, $itemID)
    {
        $itemData = $this->getUserItem($userID, $itemID);
        if (sizeof($itemData) <= 0) {
            return [];
        }
        $stats = array(
            "STR" => $itemData["Strength"],
            "DEF" => $itemData["Defence"],
            "INT" => $itemData["Intelligence"],
            "SPD" => $itemData["Speed"]
        );
        return $stats;
    }

    public function getClassStats($classID)
    {
        $classData = $this->getClassByID($classID);
        if (sizeof($classData) <= 0) {
            return [];
        }
        $stats = array(
            "STR" => $classData["Strength"],
            "DEF" => $classData["Defence"],
            "INT" => $classData["Intelligence"],
            "SPD" => $classData["Speed"]
        );
        return $stats;
    }

    public function getRaceStats($raceID)
    {
        $raceData = $this->getRaceByID($raceID);
        if (sizeof($raceData) <= 0) {
            return [];
        }
        $stats = array(
            "STR" => $raceData["Strength"],
            "DEF" => $raceData["Defence"],
            "INT" => $raceData["Intelligence"],
            "SPD" => $raceData["Speed"]
        );
        return $stats;
    }

    public function getOverallDamage($skillID, $userID)
    {
        $user = $this->getUserData($userID);
        if (sizeof($user) <= 0) {
            return [];
        }
        $userStats = $this->getUserStats($userID);
        if (sizeof($userStats) <= 0) {
            return [];
        }
        $classStats = $this->getClassStats($user["ClassID"]);
        if (sizeof($classStats) <= 0) {
            return [];
        }
        $raceStats = $this->getRaceStats($user["RaceID"]);
        if (sizeof($raceStats) <= 0) {
            return [];
        }
        $userItemEquipped = $this->getUserEquippedWeapon($userID);
        if (sizeof($userItemEquipped) <= 0) {
            return [];
        }
        $userItemEquippedID = $userItemEquipped["id"];
        $userItemStats = $this->getUserItemStats($userID, $userItemEquippedID);
        if (sizeof($userItemStats) <= 0) {
            return [];
        }
        $skillData = $this->getSkillData($skillID);
        if ($skillData->num_rows <= 0) {
            return [];
        }
        $skillData = $skillData->fetch_array();

        $overall =
            $userStats["STR"] +
            $classStats["STR"] +
            $raceStats["STR"] +
            $userItemStats["STR"];
        return $skillData["Damage"] * $overall;
    }

    public function getOverallUserSpeedStat($userID)
    {
        $user = $this->getUserData($userID);
        if (sizeof($user) <= 0) {
            return [];
        }
        $userStats = $this->getUserStats($userID);
        if (sizeof($userStats) <= 0) {
            return [];
        }
        $classStats = $this->getClassStats($user["ClassID"]);
        if (sizeof($classStats) <= 0) {
            return [];
        }
        $raceStats = $this->getRaceStats($user["RaceID"]);
        if (sizeof($raceStats) <= 0) {
            return [];
        }
        $userItemEquipped = $this->getUserEquippedWeapon($userID);
        if (sizeof($userItemEquipped) <= 0) {
            return [];
        }
        $userItemEquippedID = $userItemEquipped["id"];
        $userItemStats = $this->getUserItemStats($userID, $userItemEquippedID);
        if (sizeof($userItemStats) <= 0) {
            return [];
        }
        $overall =
            $userStats["SPD"] +
            $classStats["SPD"] +
            $raceStats["SPD"] +
            $userItemStats["SPD"];
        return $overall;
    }
    #endregion

    #region Monster Functions
    public function getAllMonsters()
    {
        $monsters = array();

        $query = "SELECT * FROM monsters";
        $query = $this->database->executeQuery($query);

        while ($row = $query->fetch_array()) {
            $index = sizeof($monsters);
            $monsters[$index]["MonsterID"] = $row['id'];
            $monsters[$index]["MonsterName"] = $row['Name'];
            $monsters[$index]["BaseHP"] = $row["BaseHP"];
            $monsters[$index]["BaseSpeed"] = $row["BaseSpeed"];
            $monsters[$index]["Level"] = $row["Level"];
        }

        return $monsters;
    }

    public function getMonsterByID($monID)
    {
        $query = "SELECT * FROM monsters WHERE id={$monID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        } else {
            return $query->fetch_array();
        }
    }

    public function getMonsterSkills($monsterID)
    {
        $query = "SELECT * FROM monsters_skills WHERE monsterID = {$monsterID}";
        $query = $this->database->executeQuery($query);
        if ($query->num_rows <= 0) {
            return [];
        }
        return $query->fetch_all();
    }
    #endregion

    #region Area Data
    public function getKing($ID)
    {
        $query = "SELECT * FROM users WHERE id = {$ID}";
        $userQuery = $this->database->executeQuery($query);
        if ($userQuery->num_rows <= 0) {
            return 'Unoccupied';
        } else {
            $Name = $userQuery->fetch_array();
            return $Name['Name'];
        }
    }
    #endregion
}
