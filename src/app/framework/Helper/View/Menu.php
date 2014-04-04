<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper\HelperInterfaceExtra as HelperExtra;
use \MattyG\Framework\Core\Config as Config;

class Menu implements HelperExtra
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Aura\Router\Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $menuConfig;

    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName)
    {
        $this->config = $config;
        $this->loadMenuConfig($helperName);
    }

    /**
     * @param array $helpers
     */
    public function giveHelpers(array $helpers)
    {
        foreach ($helpers as $helper => $instance) {
            switch ($helper)
            {
                case "router":
                    $this->router = $instance;
                    break;
            }
        }
    }

    /**
     * @param string $menuName
     */
    protected function loadMenuConfig($menuName)
    {
        if (!is_string($menuName)) {
            throw new \InvalidArgumentException("Invalid menu type.");
        }
        $menuName = str_replace("_", "/", $menuName);
        $this->menuConfig = $this->config->getConfig("menus/$menuName");
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        return $this->menuConfig;
    }

    /**
     * @param string name
     * @param array $params
     * @return bool
     */
    public function isActiveRoute($routeName, array $params = array())
    {
        $matchedRoute = $this->router->getMatchedRoute();
        if (!$matchedRoute) {
            // This means that either matching hasn't taken place, or that no
            // match was found.
            return false;
        }
        if ($matchedRoute->name !== $routeName) {
            // Wrong route name, pure and simple.
            return false;
        }

        // In order to ensure this is an exact match, we need to check any
        // matched parameters on the route.
        $namedParams = stripNumericKeys($matchedRoute->matches);
        foreach ($namedParams as $key => $value) {
            if (!isset($params[$key])) {
                return false;
            }
            if ($params[$key] != $value) {
                return false;
            }
            unset($params[$key]);
        }
        // If there are unchecked parameters in the user-supplied array, it
        // probably means the route they were checking against had optional
        // parameters that were not supplied in the request URI.
        if (count($params)) {
            return false;
        }
        return true;
    }
}

