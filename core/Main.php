<?php
// Session Management
session_start();

// Imports
require('config.php');
require('handlers/DatabaseHandler.php');
require('handlers/TemplateHandler.php');
require('handlers/Functions.php');

if(Configuration::devMode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

class Main
{

    public $template;
    public $db;
    public $functions;

    private $userLoggedOutPages;

    /**
     * Main constructor.
     */
    public function __construct($useTemplate = true)
    {
        if (Configuration::useDatabase) {
            // Initialize Database
            $this->db = new DatabaseHandler();
            $this->preventInjection();
        }

        // Initialize Functions
        $this->functions = new Functions($this);

        if($useTemplate) {
            // Initialize Template System
            $this->template = new TemplateHandler("site.design");
            $this->setDefaultTemplateSettings();
        }

        // See if user is logged in.
        $this->userLoggedOutPages = array("login", "signup");
        if ($this->functions->verifyUserLoggedIn()) {
            if (in_array(basename($_SERVER['PHP_SELF'], '.php'), $this->userLoggedOutPages)) {
                header('Location: '.$this->getWorkingDirectory());
            }
        } else {
            if (!in_array(basename($_SERVER['PHP_SELF'], '.php'), $this->userLoggedOutPages)) {
                header('Location: '.$this->getWorkingDirectory().'login.php');
            }
        }

    }

    /**
     *
     */
    private function preventInjection()
    {
        $_POST = $this->db->escapeArray($_POST);
        $_GET = $this->db->escapeArray($_GET);
    }

    public function getWorkingDirectory() {
        return Configuration::devMode ? Configuration::devDirectory : "/";
    }

    /**
     *
     */
    private function setDefaultTemplateSettings()
    {
        $navbar = null;
        if($this->functions->verifyUserLoggedIn()) {
            $navbar = new TemplateHandler("components/component.navbar.loggedin");
        } else {
            $navbar = new TemplateHandler("components/component.navbar");
        }
        $this->template->setVariable("siteName", Configuration::siteName);
        $navbar->setVariable("siteName", Configuration::siteName);
        $navbar->setVariable("currentDirectory", $this->getWorkingDirectory());

        if($this->functions->verifyUserLoggedIn()) {
            // Populate with User Data
            $this->populateNavbarWithUserData($navbar);
        }

        $this->template->setVariable("siteNavbar", $navbar->getTemplate());
        $this->template->setVariable("currentYear", date("Y"));
    }

    private function populateNavbarWithUserData($navbar) {
        $currentUser = $this->functions->getCurrentUser();
        $navbar->setVariable("userName", $currentUser["Name"]);
        $navbar->setVariable("userLevel", $currentUser["Level"]);
        $navbar->setVariable("userGold", $currentUser["Gold"]);
        $navbar->setVariable("userEXP", $currentUser["EXP"]);
    }
}
