<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Helper\HelperInterface as Helper;
use \MattyG\Framework\Core\Config as Config;
use \Aura\Router\Router as Router;

class Url implements Helper
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
     * @param \Aura\Router\Router $router
     * @return Url
     */
    public function setRouterObject(Router $router)
    {
        $this->router = $router;
        return $this;
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
     * @param array $params
     * @return string
     */
    public function getUrl($path, array $params = array())
    {
        $url = rtrim($this->getBaseUrl() . $path, "/") . "/";
        $url .= http_build_query($params);
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
        return $this->router->generate($routeName, $params);
    }
}

