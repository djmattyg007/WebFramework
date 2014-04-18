<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper\HelperInterfaceExtra as HelperExtra;
use \MattyG\Framework\Core\Config as Config;
use \MattyG\Framework\Helper\Core\Url as UrlHelper;

class AssetManager implements HelperExtra
{
    const DIR_ASSETS = "assets";

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \MattyG\Framework\Helper\Core\Url
     */
    protected $urlHelper;

    /**
     * @var array
     */
    protected $assets;

    /**
     * @var string
     */
    protected $assetsDir = self::DIR_ASSETS;

    /**
     * @var string
     */
    protected $assetsBaseUrl = null;

    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName)
    {
        $this->config = $config;
        $this->assets = array();
        $this->loadAssetList($helperName);
    }

    /**
     * @param array $helpers
     */
    public function giveHelpers(array $helpers)
    {
        foreach ($helpers as $helper => $instance) {
            switch ($helper)
            {
                case "url":
                    $this->urlHelper = $instance;
                    break;
            }
        }
    }

    /**
     * @param string $assetLIst
     * @return void
     */
    public function loadAssetList($assetList, $clearFirst = true)
    {
        if ($assetList === null) {
            return;
        }
        if (!is_string($assetList)) {
            throw new \InvalidArgumentException("Invalid asset list type.");
        }
        if ($clearFirst === true) {
            $this->clearAssets();
        }
        $assets = $this->config->getConfig("assets/$assetList");
        foreach ($assets as $asset) {
            $this->assets[] = $asset["filename"];
        }
    }

    /**
     * @param string $asset
     * @return AssetManager
     */
    public function addAsset($asset)
    {
        if (!is_string($asset) && !(is_object($asset) && method_exists($asset, "__toString"))) {
            throw new \InvalidArgumentException("Invalid asset filename supplied.");
        }
        $this->assets[] = (string) $asset;
        return $this;
    }

    /**
     * @return AssetManager
     */
    public function clearAssets()
    {
        $this->assets = array();
        return $this;
    }

    /**
     * @return string
     */
    public function display()
    {
        $results = array();

        foreach ($this->assets as $asset) {
            $results[] = $this->assetTag($asset);
        }

        return implode("\n", $results);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function assetTag($filename)
    {
        $url = $this->getAssetsBaseUrl() . $filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($extension)
        {
            case "css":
                return sprintf('<link rel="stylesheet" type="text/css" href="%s" media="all">', $url);
            case "js":
                return sprintf('<script type="text/javascript" src="%s"></script>', $url);
            default:
                return "";
        }
    }

    /**
     * @return string
     */
    public function getAssetsBaseUrl()
    {
        if ($this->assetsBaseUrl === null) {
            $this->assetsBaseUrl = $this->urlHelper->getBaseUrl() . $this->assetsDir . "/";
        }
        return $this->assetsBaseUrl;
    }
}

