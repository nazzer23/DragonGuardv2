<?php

class Functions
{
    private $global;

    public function __construct($global)
    {
        $this->global = $global;
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
}
