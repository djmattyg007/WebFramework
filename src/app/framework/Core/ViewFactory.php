<?php

namespace MattyG\Framework\Core;

class ViewFactory
{
    /**
     * @var string
     */
    protected $viewDirectory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param string $viewDirectory
     * @param Config $config
     */
    public function __construct($viewDirectory, Config $config)
    {
        $this->viewDirectory = rtrim($viewDirectory, "/") . "/";
        $this->config = $config;
    }

    /**
     * @param string $pageView
     * @return string
     */
    public function getViewFileName($pageView)
    {
        $path = str_replace(".", "/", $pageView);
        return $this->viewDirectory . $path . ".php";
    }

    /**
     * @param array $pageName
     * @param bool $directOutput
     * @return View
     */
    public function newView($viewData, $directOutput = false)
    {
        $viewFile = $this->getViewFileName($viewData["view"]);
        $children = $this->buildBlocks($viewData["children"]);
        return new View($viewFile, $this, $children, $directOutput);
    }

    public function buildBlocks(array $blocks)
    {
        $returnBlocks = array();
        foreach ($blocks as $block) {
            $returnBlocks[$block["name"]] = $block;
        }
        return $returnBlocks;
    }

    /**
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
        return new View($this->getViewFileName($pageLayout["view"]), $this, $rootBlocks, $directOutput);
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

