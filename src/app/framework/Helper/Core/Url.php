<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Helper\HelperInterfaceExtra as HelperExtra;
use \MattyG\Framework\Core\Config as Config;
use \Aura\Router\Router as Router;

class Url implements HelperExtra
{
    /**
     * @var MattyG\Framework\Core\Config
     */
    protected $config;

    /**
     * @var \Aura\Router\Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName)
    {
        $this->config = $config;
        $this->baseUrl = $this->config->getConfig("site/base_url");
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
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $path
     * @param array $queryParams
     * @return string
     */
    public function getUrl($path, array $queryParams = array())
    {
        $url = rtrim($this->getBaseUrl() . $path, "/") . "/";
        $url .= http_build_query($queryParams);
        return $url;
    }

    /**
     * @param string $routeName
     * @param array $params
     * @return string
     */
    public function getRouteUrl($routeName, array $params = array())
    {
        if (!$this->router) {
            return null;
        }
        return rtrim($this->getBaseUrl(), "/") . $this->router->generate($routeName, $params);
    }
}

