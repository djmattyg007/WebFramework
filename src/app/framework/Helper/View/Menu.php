<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper\HelperInterface as Helper;
use \MattyG\Framework\Core\Config as Config;

class Menu implements Helper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MattyG\Framework\Helper\Core\Url
     */
    protected $urlHelper;

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
}

