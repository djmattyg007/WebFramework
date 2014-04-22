<?php

namespace MattyG\Framework\Core;

use \Aura\Router\Router as Router;
use \Aura\Di\Container as DIContainer;

class Config
{
    const CONFIG_CACHE_ENTRY_NAME = "core_config";

    const DIR_CONFIG = "config";

    /**
     * The base directory of the application, which should contain the config,
     * public, var and views directories.
     *
     * @var string
     */
    protected $baseDirectory;

    /**
     * The config directory in the application. It contains all configuration
     * for the application, with each set in a pool.
     *
     * @var string
     */
    protected $configDirectory;

    /**
     * A reference to the App's Cache object. It's primary use is to load the
     * configuration from the cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * A reference to the App's dependency injection container. It's primary
     * use in the Config class is to supply helpers to the view manager.
     * //TODO: look to see if this should be refactored.
     *
     * @var \Aura\Di\Container
     */
    protected $diContainer;

    /**
     * A reference to the App's Router object.
     * //TODO: see if this reference is actually being used, especially since
     * it's now inserted into the DI container.
     *
     * @var \Aura\Router\Router
     */
    protected $router = null;

    /**
     * The pools in use by the application. By default, this will contain
     * "framework" and "user", and it should be in that order.
     *
     * @var array
     */
    protected $pools;

    /**
     * Whether or not strict mode is enabled. If it is, a RuntimeException
     * will be thrown when a non-serious error occurs. If it is not, the error
     * will be handled sensibly.
     *
     * @var bool
     */
    protected $strict;

    /**
     * The top-level configuration tree. Holds all configuration once it is
     * read into memory.
     *
     * @var array
     */
    protected $configTree;

    /**
     * //TODO: see if this is still necessary. It seems completely unnecessary
     * to cache objects grabbed from the DI container.
     *
     * @var array
     */
    protected $helpers;

    /**
     * @param string $baseDirectory
     * @param array $pools
     * @param Cache $cache
     * @param \Aura\Di\Container $diContainer
     * @param bool $strict
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($baseDirectory, array $pools, Cache $cache = null, DIContainer $diContainer, $strict = false)
    {
        $this->baseDirectory = rtrim($baseDirectory, "/") . "/";
        $configDirectory = $this->baseDirectory . self::DIR_CONFIG;
        if (!is_string($configDirectory)) {
            throw new \InvalidArgumentException("Invalid parameter supplied for where to find configuration.");
        }
        $configDirectory = realpath($configDirectory);
        if (!file_exists($configDirectory)) {
            throw new \InvalidArgumentException("Configuration directory does not exist.");
        }
        if (!is_readable($configDirectory)) {
            throw new \RuntimeException("Unable to read configuration directory.");
        }
        $this->configDirectory = rtrim($configDirectory, "/") . "/";

        if (!is_bool($strict)) {
            throw new \InvalidArgumentException("Invalid parameter supplied for whether or not to use strict mode.");
        }
        $this->strict = $strict;

        $this->pools = $pools;
        $this->cache = $cache;
        $this->diContainer = $diContainer;
        $this->helpers = array();
        $this->initialiseConfigTree();
    }

    /**
     * The pools in use by the application.
     *
     * @return array
     */
    public function getPools()
    {
        return $this->pools;
    }

    /**
     * Retrieves the Cache object in use by the App class.
     *
     * @return Cache
     */
    public function getCacheObject()
    {
        return $this->cache;
    }

    /**
     * @param \Aura\Router\Router $router
     * @return Config
     */
    public function setRouterObject(Router $router)
    {
        $this->router = $router;
        $this->helpers["router"] = $router;
        return $this;
    }

    /**
     * @return \Aura\Router\Router
     */
    public function getRouterObject()
    {
        return $this->router;
    }

    /**
     * Gets the value of a configuration setting defined within the
     * application's config directory.
     * Config paths take the form "path/to/config/value".
     * To access a value in an array of values indexed numerically, provide a
     * config path of the form "path/to/ * /name=myname/value". Note that the
     * spaces around the asterisk are cosmetic, because of the nature of PHP
     * comments. Do not uses spaces when actually writing code.
     * If you omit the configPath parameter, the entire config tree will be
     * returned.
     * If you supply an array for the currentConfig parameter, that array will
     * be searched instead of the global config tree.
     *
     * @param string $configPath
     * @param array $currentConfig
     * @return array|mixed
     * @throws \RuntimeException
     */
    public function getConfig($configPath = null, array $currentConfig = null)
    {
        if (!$configPath) {
            return $this->configTree;
        }
        if (!$currentConfig) {
            $currentConfig = $this->configTree;
        }

        $path = explode("/", $configPath);
        $pathCount = count($path);
        for ($index = 0; $index < $pathCount; $index++) {
            if ($path[$index] === "*") {
                if (isAssoc($currentConfig)) {
                    return null;
                }
                if (!isset($path[($index + 1)])) {
                    if ($this->strict === true) {
                        throw new \RuntimeException("Invalid configuration path.");
                    } else {
                        return null;
                    }
                }
                $search = explode("=", $path[($index + 1)]);
                if (count($search) !== 2) {
                    if ($this->strict === true) {
                        throw new \RuntimeException("Invalid configuration path.");
                    } else {
                        return null;
                    }
                }
                $column = array_column($currentConfig, $search[0]);
                $searchIndex = array_search($search[1], $column);
                if ($searchIndex === false) {
                    return null;
                } else {
                    $currentConfig = $currentConfig[$searchIndex];
                    $index++;
                }
            } else {
                if (!isset($currentConfig[$path[$index]])) {
                    return null;
                }
                $currentConfig = $currentConfig[$path[$index]];
            }
        }
        return $currentConfig;
    }

    /**
     * Retrieves a helper.
     * Currently this is just a proxy for the dependency injection container.
     * //TODO: review how to better provide access to helpers.
     *
     * @param string $name
     * @return \MattyG\Framework\Core\Helper
     */
    public function getHelper($name)
    {
        if ($this->diContainer->has($name)) {
            return $this->diContainer->get($name);
        } else {
            return null;
        }
    }

    /**
     * Returns the main directory containing all of the application files.
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Returns the main directory that all configuration files must be
     * contained within.
     *
     * @return string
     */
    public function getConfigDirectory()
    {
        return $this->configDirectory;
    }

    /**
     * Save the current configuration tree into the cache.
     *
     * @return void
     */
    protected function saveConfigCache()
    {
        if (!$this->cache) {
            return;
        }
        $this->cache->saveData(self::CONFIG_CACHE_ENTRY_NAME, json_encode($this->configTree), (time() + 3600));
    }

    /**
     * Attempt to grab the configuration tree from the cache.
     *
     * @return array|null
     */
    protected function getConfigCache()
    {
        if (!$this->cache) {
            return null;
        }
        return json_decode($this->cache->loadData(self::CONFIG_CACHE_ENTRY_NAME, null), true);
    }


    /** Initialise configuration **/

    /**
     * Initialise the configuration tree.
     * Optionally checks to see if the configuration tree is in the cache
     * first, and if so, uses that rather than regenerating the tree.
     *
     * @param bool $useCache
     * @return void
     */
    protected function initialiseConfigTree($useCache = true)
    {
        if ($useCache === true && ($configCache = $this->getConfigCache())) {
            $this->configTree = $configCache;
            return;
        }
        $this->configTree = array();
        foreach ($this->pools as $pool) {
            $this->readConfig($pool);
        }
        $this->saveConfigCache();
    }

    /**
     * Take in a pool (by default, either "framework" or "user", work out which
     * directory their configuration files are in and process all configuration
     * files inside that directory.
     *
     * @param string $pool
     * @throws \RuntimeException
     */
    protected function readConfig($pool)
    {
        $path = $this->configDirectory . $pool . "/";
        if (!file_exists($path)) {
            throw new \RuntimeException("Configuration directory for $pool pool is missing.");
        }
        $this->readConfigFiles($path);
    }

    /**
     * Read all JSON configuration files from the given directory and process
     * them. Subdirectories will be traversed into if they match the name of a
     * JSON file.
     *
     * @param string $path Should always have a trailing slash.
     * @throws \RuntimeException
     */
    protected function readConfigFiles($path)
    {
        $files = glob($path . "*.json");
        foreach ($files as $file) {
            if (!is_readable($file)) {
                if ($this->strict) {
                    throw new \RuntimeException("Configuration file $file is not readable.");
                } else {
                    continue;
                }
            }
            $config = file_get_contents($file);
            $config = json_decode($config, true);
            if (!is_array($config)) {
                if ($this->strict) {
                    throw new \RuntimeException("Configuration file $file contains invalid JSON.");
                } else {
                    continue;
                }
            }
            $this->fillTree($this->configTree, $config);

            $subPath = str_replace(".json", "/", $file);
            if (file_exists($subPath)) {
                $this->readConfigFiles($subPath);
            }
        }
    }

    /**
     * Process the config array, filling up the tree array, overriding values
     * if necessary.
     *
     * @param array $tree
     * @param array $config
     */
    protected function fillTree(array &$tree, array $config)
    {
        foreach ($config as $key => $value) {
            if (is_numeric($key)) {
                $searchIndex = false;
                if (isset($value["name"])) {
                    $column = array_column($tree, "name");
                    $searchIndex = array_search($value["name"], $column);
                }
                if ($searchIndex === false) {
                    $tree[] = array();
                    $actualKey = count($tree) - 1;
                } else {
                    $actualKey = $searchIndex;
                }
            } else {
                $actualKey = $key;
            }

            if (is_array($value)) {
                if (!isset($tree[$actualKey])) {
                    $tree[$actualKey] = array();
                }
                $this->fillTree($tree[$actualKey], $value);
            } else {
                $tree[$actualKey] = $value;
            }
        }
    }
}
