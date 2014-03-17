<?php

namespace MattyG\Framework\Core;

class Config
{
    const CONFIG_CACHE_ENTRY_NAME = "config";

    /**
     * @var string
     */
    protected $configDirectory;

    /**
     * @var array
     */
    protected $pools;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @var array
     */
    protected $configTree;

    /**
     * @param string $configDirectory
     * @param array $pools
     * @param Cache $cache
     * @param bool $strict
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($configDirectory, array $pools, Cache $cache = null, $strict = false)
    {
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
        $this->initialiseConfigTree();
    }

    /**
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
        $this->cache->saveData(self::CONFIG_CACHE_ENTRY_NAME, json_encode($this->configTree), (time() + 300));
    }

    /**
     * Attempt to grab the configuration tree from the cache.
     *
     * @return mixed|null
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
     * first, and if so, uses that rather than regenerate the tree.
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
                $tree[] = array();
                $actualKey = count($tree) - 1;
            } else {
                $actualKey = $key;
            }

            if (is_array($value)) {
                if (!isset($tree[$actualKey])) {
                    $tree[$key] = array();
                }
                $this->fillTree($tree[$actualKey], $value);
            } else {
                $tree[$actualKey] = $value;
            }
        }
    }
}
