<?php

namespace MattyG\Framework\Core\View;

use \MattyG\Framework\Core\Config as Config;

use \MattyG\Framework\Core\Helper\HelperInterfaceExtra as HelperExtra;

class Manager
{
    const DIR_VIEW = "views";

    /**
     * The directory that contains all views in the application.
     *
     * @var string
     */
    protected $viewDirectory;

    /**
     * @var MattyG\Framework\Core\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $globalVars;

    /**
     * @var array
     */
    protected $viewHelpers;

    /**
     * @param string $baseDirectory
     * @param Config $config
     */
    public function __construct($baseDirectory, Config $config)
    {
        $this->viewDirectory = rtrim($baseDirectory, "/") . "/" . self::DIR_VIEW . "/";
        $this->config = $config;
        $this->globalVars = array();
        $this->viewHelpers = array();
        $this->initialiseGlobalHelpers();
    }

    /**
     * @param array $vars
     * @return Manager
     */
    public function setVars(array $vars)
    {
        $this->globalVars = $vars;
        return $this;
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->globalVars;
    }

    /**
     * @param string $var
     * @param mixed $value
     * @return Manager
     */
    public function addVar($var, $value)
    {
        $this->globalVars[$var] = $value;
        return $this;
    }

    /**
     * @param string
     */
    public function getHelper($name)
    {
        if (!isset($this->viewHelpers[$name])) {
            $helper = $this->config->getConfig("helpers/view/*/name=$name");
            if (!$helper) {
                $helper = $this->config->getConfig("helpers/core/*/name=$name");
            }
            $helperClass = $helper["class"];
            $this->viewHelpers[$name] = new $helperClass($this->config, $helper["name"]);
            if ($this->viewHelpers[$name] instanceof HelperExtra) {
                $requirements = array();
                foreach ($helper["require"] as $requirement) {
                    $requirements[$requirement["name"]] = $this->getHelper($requirement["name"]);
                }
                $this->viewHelpers[$name]->giveHelpers($requirements);
            }
        }
        return $this->viewHelpers[$name];
    }

    /**
     * Initialises helper objects for all objects listed in the
     * layout/global_helpers config node and sets them as global variables for
     * all views.
     */
    public function initialiseGlobalHelpers()
    {
        $globalHelpers = $this->config->getConfig("layout/global_helpers");
        foreach ($globalHelpers as $helper) {
            $this->addVar("helper" . ucfirst($helper["name"]), $this->getHelper($helper["name"]));
        }
    }

    /**
     * Convert a view name of the format "path.to.view" to a filename with an
     * absolute path.
     *
     * @param string $pageView
     * @return string
     */
    public function getViewFileName($pageView)
    {
        $path = str_replace(".", "/", $pageView);
        return $this->viewDirectory . $path . ".php";
    }

    /**
     * Construct a new View object based off of a view name and some
     * information about its child views.
     *
     * @param array $pageName
     * @param bool $directOutput Controls the use of output buffering when
     *      rendering the View object.
     * @return View
     */
    public function newView($viewData, $directOutput = false)
    {
        $viewFile = $this->getViewFileName($viewData["view"]);
        $children = $this->buildBlocks($viewData["children"]);
        $view = new View($viewFile, $this, $children, $directOutput);
        $helpers = $this->getViewHelpers($viewData["helpers"]);
        $view->setVars(array_merge($helpers, $this->getVars()));
        return $view;
    }

    /**
     * Build an array of block names.
     * TODO: Investigate whether or not array_column is usable here.
     *
     * @param array $blocks
     * @return array
     */
    public function buildBlocks(array $blocks)
    {
        $returnBlocks = array();
        foreach ($blocks as $block) {
            $returnBlocks[$block["name"]] = $block;
        }
        return $returnBlocks;
    }

    /**
     * @param array $helpers
     * @return array
     */
    public function getViewHelpers(array $helpers)
    {
        $return = array();
        foreach ($helpers as $helper) {
            $helperName = "helper" . ucfirst($helper["name"]);
            $return[$helperName] = $this->getHelper($helper["name"]);
        }
        return $return;
    }

    /**
     * Construct a new View object for a full page.
     * The page must be defined in layout/base/pages in the configuration
     * hierarchy.
     *
     * @param string $routeName
     * @param string $pageName
     * @param bool $directOutput
     * @return View
     */
    public function newRootView($routeName, $pageName = "", $directOutput = false)
    {
        if (!$pageName) {
            $routeLayout = $this->config->getConfig("layout/routes/*/name=" . $routeName);
            $pageName = $routeLayout["page"];
        }
        $pageLayout = $this->config->getConfig("layout/base/pages/*/name=" . $pageName);
        $rootBlocks = $this->buildRootBlocks($pageName, $routeName);
        $view = new View($this->getViewFileName($pageLayout["view"]), $this, $rootBlocks, $directOutput);
        $view->setVars($this->getVars());
        return $view;
    }

    /**
     * @param string $pageName
     * @param string $routeName
     * @return array
     */
    public function buildRootBlocks($pageName, $routeName)
    {
        $pageLayout = $this->config->getConfig("layout/base/pages/*/name=" . $pageName);
        $routeLayout = $this->config->getConfig("layout/routes/*/name=" . $routeName);
        $rootBlocks = array();
        foreach ($pageLayout["blocks"] as $blockName) {
            $block = $this->config->getConfig("blocks/*/name=" . $blockName["name"], $routeLayout);
            if ($block === null) {
                $block = $this->config->getConfig("layout/base/blocks/*/name=" . $blockName["name"]);
            }
            $rootBlocks[$blockName["name"]] = $block;
        }
        return $rootBlocks;
    }
}

