<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper as Helper;
use \MattyG\Framework\Core\Config as Config;
use \MattyG\Framework\Helper\Core\Url as UrlHelper;

class AssetManager implements Helper
{
    const DIR_ASSETS = "assets";

    /**
     * @var \MattyG\Framework\Core\Config
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
     * @param \MattyG\Framework\Core\Config $config
     * @param string $helperName
     * @param \MattyG\Framework\Helper\Core\Url $url
     */
    public function __construct(Config $config, $helperName, UrlHelper $url = null)
    {
        $this->config = $config;
        $this->urlHelper = $url;
        $this->assets = array();
        $this->loadAssetList($helperName);
    }

    /**
     * @param string $assetList
     * @param bool $clearFirst
     * @return void
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
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

