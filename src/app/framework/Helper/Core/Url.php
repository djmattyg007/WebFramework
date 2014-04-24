<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Helper as Helper;
use \MattyG\Framework\Core\Config as Config;
use \Aura\Router\Router as Router;

class Url implements Helper
{
    /**
     * @var \MattyG\Framework\Core\Config
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
     * @param \MattyG\Framework\Core\Config $config
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
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Returns the "base_url" configuration setting.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Generates a full URL with the application's base URL.
     * It also supports automatic addition of query parameters.
     *
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
     * Generates a full URL for a specific route, as defined in routes.php.
     *
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

