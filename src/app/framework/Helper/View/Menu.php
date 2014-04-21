<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper as Helper;
use \MattyG\Framework\Core\Config as Config;
use \MattyG\Framework\Helper\Core\Url as UrlHelper;
use \Aura\Router\Router as Router;

class Menu implements Helper
{
    const CLASS_ACTIVE = "active";

    /**
     * @var \MattyG\Framework\Core\Config
     */
    protected $config;

    /**
     * @var \MattyG\Framework\Helper\Core\Url
     */
    protected $urlHelper;

    /**
     * @var \Aura\Router\Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $nodes;

    /**
     * @param \MattyG\Framework\Core\Config $config
     * @param string $helperName
     * @param \MattyG\Framework\Helper\Core\Url $url
     */
    public function __construct(Config $config, $helperName, UrlHelper $url = null)
    {
        $this->config = $config;
        $this->urlHelper = $url;
        $this->loadMenuConfig($helperName);
    }

    /**
     * @param \Aura\Router\Router $router
     * @return Menu
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param string $menuName
     * @throws \InvalidArgumentException
     */
    protected function loadMenuConfig($menuName)
    {
        if (!is_string($menuName)) {
            throw new \InvalidArgumentException("Invalid menu type.");
        }
        $menuName = str_replace("_", "/", $menuName);
        $menuConfig = $this->config->getConfig("menus/$menuName");
        $this->settings = $menuConfig["settings"];
        $this->nodes = $menuConfig["nodes"];
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        return $this->nodes;
    }

    /**
     * @param string $key
     * @return array|mixed|null
     */
    public function getMenuSetting($key = null)
    {
        if ($key === null) {
            return $this->settings;
        }
        return $this->config->getConfig("*/name=$key/value", $this->settings);
    }

    /**
     * @param string $routeName
     * @param array $params
     * @return bool
     */
    public function isActiveRoute($routeName, array $params = array())
    {
        if (!$this->router) {
            // If we don't have a Router object, we have no way of knowing.
            // Don't lie!
            return false;
        }
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

