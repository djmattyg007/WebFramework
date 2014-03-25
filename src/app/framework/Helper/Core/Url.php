<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Helper\HelperInterface as Helper;
use \MattyG\Framework\Core\Config as Config;

class Url implements Helper
{
    /**
     * @var MattyG\Framework\Core\Config
     */
    protected $config;

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
}

