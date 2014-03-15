<?php

namespace MattyG\Framework\Autoload;

class Autoloader
{
    const CONFIG_DIR = "config";

    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @var array
     */
    protected $prefixes = array();

    /**
     * @param string $baseDirectory
     * @param array $pools
     * @param bool $strict
     */
    public function __construct($baseDirectory, $pools, $strict = true)
    {
        $this->baseDirectory = rtrim($baseDirectory, "/") . "/";
        $this->strict = $strict;
        $this->registerAutoloader();
        $this->loadPrefixes($pools);
    }

    public function registerAutoloader()
    {
        spl_autoload_register(array($this, "autoload"), true);
    }

    public function unregisterAutoloader()
    {
        spl_autoload_unregister(array($this, "autoload"));
    }

    /**
     * @param array $pools
     * @throws \RuntimeException
     */
    public function loadPrefixes(array $pools)
    {
        foreach ($pools as $pool) {
            $configFile = $this->baseDirectory . "autoload/" . self::CONFIG_DIR . "/autoload." . $pool . ".json";
            if (!file_exists($configFile)) {
                if ($this->strict === true) {
                    throw new \RuntimeException(ucfirst($pool) . " autoload configuration file is missing.");
                } else {
                    continue;
                }
            }
            if (!is_readable($configFile)) {
                if ($this->strict === true) {
                    throw new \RuntimeException(ucfirst($pool) . " autoload configuration file is not readable.");
                } else {
                    continue;
                }
            }
            $autoloadConfig = file_get_contents($configFile);
            $autoloadConfig = json_decode($autoloadConfig, true);

            foreach ($autoloadConfig["autoload"] as $prefix) {
                $this->addPrefix($prefix);
            }
        }
    }

    /**
     * @param array $prefix
     * @param bool $prepend
     * @throws \RuntimeException
     */
    public function addPrefix(array $prefix, $prepend = false)
    {
        $directoryCheck = $this->baseDirectory . $prefix["directory"];
        if (!file_exists($directoryCheck)) {
            if ($this->strict === true) {
                throw new \RuntimeException("Directory specified for autoloading {$prefix["name"]} does not exist.");
            } else {
                return;
            }
        }
        if (!is_readable($directoryCheck)) {
            if ($this->strict === true) {
                throw new \RuntimeException("Directory specified for autoloading {$prefix["name"]} is not readable.");
            } else {
                return;
            }
        }

        if ($prepend === true) {
            array_unshift($this->prefixes, $prefix);
        } else {
            $this->prefixes[] = $prefix;
        }
    }

    /**
     * @param string $className
     * @return bool
     */
    public function autoload($className)
    {
        if ($fileName = $this->getFileName($className)) {
            include($fileName);
            return true;
        }
        return false;
    }

    /**
     * @param string $className
     * @return string|null
     */
    public function getFileName($className)
    {
        foreach ($this->prefixes as $prefix) {
            if (substr($className, 0, strlen($prefix["prefix"])) == $prefix["prefix"]) {
                $fileName = $this->baseDirectory . $prefix["directory"] . substr($className, strlen($prefix["prefix"])) . ".php";
                $fileName = str_replace("\\", "/", $fileName);
                if (file_exists($fileName)) {
                    return $fileName;
                }
            }
        }
        return null;
    }
}

