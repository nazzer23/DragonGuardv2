<?php

class TemplateHandler
{
    private $template;
    private $vars = [];

    public function __construct($templateFile)
    {
        $this->template = $this->getFile($templateFile);
    }

    public function getFile($file)
    {
        $file = "core/template/{$file}.html";
        if (file_exists($file)) {
            $file = file_get_contents($file);
            return $file;
        } else {
            return $this->execute404($file);
            //die("The file that was requested ({$file}) cannot be loaded. Please try again later or contact the site administrator.");
        }
    }

    private function execute404($originalFile) {
        $this->template = $this->getFile("site.404");
        if(!Configuration::devMode){
            $this->setVariable("errorMessage", Configuration::DefaultNotFound);
        } else {
            $this->setVariable("errorMessage", "The file that was requested ({$originalFile}) cannot be loaded.");
        }
        return $this->getTemplate();
    }

    public function appendVariable($variable, $value)
    {
        if (!array_key_exists($variable, $this->vars)) {
            $this->vars[$variable] = $value;
        } else {
            $this->vars[$variable] .= $value;
        }
    }

    public function setVariable($variable, $value)
    {
        $this->vars[$variable] = $value;
    }

    public function getTemplate()
    {
        $this->replaceTags();
        return $this->template;
    }

    private function replaceTags()
    {
        foreach ($this->vars as $tag => $value) {
            $this->template = str_replace('{' . $tag . '}', $value, $this->template);
        }
        return true;
    }

    public function render()
    {
        $this->replaceTags();
        echo $this->template;
    }

}

?>