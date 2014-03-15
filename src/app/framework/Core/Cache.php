<?php

namespace MattyG\Framework\Core;

class Cache
{
    const DIRECTORY_CACHE = "cache";
    const DIRECTORY_OBJECTS = "objects";
    const CACHE_INFO_FILENAME = "cacheinfo.json";

    /**
     * @var string
     */
    protected $configDirectory;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @var array
     */
    protected $cacheInfo;

    /**
     * @var array
     */
    protected $cacheObjects;

    /**
     * @var array
     */
    protected $changed;

    /**
     * @param type $varDirectory
     * @param type $strict
     */
    public function __construct($varDirectory, $strict = false)
    {
        if (!is_string($varDirectory)) {
            throw new \InvalidArgumentException("Invalid parameter supplied for where to find writeable directory.");
        }
        $varDirectory = realpath($varDirectory);
        if (!file_exists($varDirectory)) {
            throw new \InvalidArgumentException("Writeable directory does not exist.");
        }
        if (!is_writeable($varDirectory)) {
            throw new \RuntimeException("Unable to write to cache directory.");
        }
        $this->cacheDirectory = rtrim($varDirectory, "/") . "/" . self::DIRECTORY_CACHE . "/";

        $this->strict = $strict;

        $this->loadCacheInformation();
        $this->cacheObjects = array();
        $this->changed = array();
    }

    /**
     * @param bool $reload
     * @return void
     */
    protected function loadCacheInformation($reload = false)
    {
        if ($this->cacheInfo && $reload === false) {
            return;
        }
        if (!file_exists($this->cacheDirectory)) {
            $this->prepareCache();
            return;
        }
        $cacheInfo = file_get_contents($this->cacheDirectory . self::CACHE_INFO_FILENAME);
        $cacheInfo = json_decode($cacheInfo, true);
        if (isset($cacheInfo["cache"]) && is_array($cacheInfo["cache"])) {
            $this->cacheInfo = $cacheInfo["cache"];
        } else {
            throw new \RuntimeException("No cache information found in cache directory.");
        }
    }

    /**
     * Ensures the cache directory has been created.
     * Should only be called if the cache directory does not exit.
     */
    protected function prepareCache()
    {
        mkdir($this->cacheDirectory);
        mkdir($this->getObjectsDirectory());

        $this->cacheInfo = array("objects" => array());
        file_put_contents($this->cacheDirectory . self::CACHE_INFO_FILENAME, json_encode(array("cache" => $this->cacheInfo)));
    }

    /**
     * @return string
     */
    protected function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }

    /**
     * @return string
     */
    protected function getObjectsDirectory()
    {
        return $this->cacheDirectory . self::DIRECTORY_OBJECTS . "/";
    }

    /**
     * Save data to the cache.
     *
     * @param string $objectId
     * @param mixed $object
     * @param int $expiry Date (in Unix timestamp form) to remove the object from the cache.
     * @param bool $persistImmediately
     */
    public function saveData($objectId, $object, $expiry = null, $persistImmediately = false)
    {
        if (!is_string($objectId)) {
            throw new \InvalidArgumentException("Invalid object ID supplied.");
        }
        if (!is_int($expiry) || $expiry < time()) {
            $expiry = null;
        }
        if (!isset($this->cacheInfo["objects"][$objectId])) {
            $this->cacheInfo["objects"][$objectId] = array();
        }
        $hash = sha1($objectId);
        $this->cacheInfo["objects"][$objectId]["store"] = $hash;
        $this->cacheInfo["objects"][$objectId]["expiry"] = $expiry;
        $this->cacheObjects[$hash] = $object;
        if ($persistImmediately === true) {
            $this->saveCacheObject($hash);
        } else {
            $this->markAsChanged($objectId);
        }
    }

    /**
     * @param string $hash
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function saveCacheObject($hash)
    {
        if (!is_string($hash)) {
            throw new \InvalidArgumentException("Invalid object ID hash supplied.");
        }
        if (!isset($this->cacheObjects[$hash])) {
            throw new \RuntimeException("Cannot save non-existent cache object.");
        }
        $fileName = $this->getObjectsDirectory() . $hash;
        if (file_exists($fileName) && !is_writeable($fileName)) {
            throw new \RuntimeException("Unable to save data for cache object $objectId.");
        }
        $check = file_put_contents($fileName, serialize($this->cacheObjects[$hash]), LOCK_EX);
        if ($check === false && $this->strict) {
            throw new \RuntimeException("Unable to save cache object $objectId.");
        }
    }

    /**
     * @param string $objectId
     */
    protected function markAsChanged($objectId)
    {
        if (!is_string($objectId)) {
            throw new \InvalidArgumentException("Invalid object ID supplied.");
        }
        if (!isset($this->cacheInfo["objects"][$objectId])) {
            throw new \RuntimeException("Specified object does not exist.");
        }
        if (!in_array($objectId, $this->changed)) {
            $this->changed[] = $objectId;
        }
    }

    /**
     * @param string $objectId
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function loadData($objectId, $default = null)
    {
        if (!is_string($objectId)) {
            throw new \InvalidArgumentException("Invalid object ID supplied.");
        }

        if (isset($this->cacheInfo["objects"][$objectId])) {
            $hash = sha1($objectId);
            //TODO: check expiry of cache object
            if (isset($this->cacheObjects[$hash])) {
                return $this->cacheObjects[$hash];
            } else {
                return $this->loadCacheObject($this->cacheInfo["objects"][$objectId]["store"]);
            }
        } else {
            return $default;
        }
    }

    /**
     * @param string $hash
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function loadCacheObject($hash)
    {
        if (!is_string($hash)) {
            throw new \InvalidArgumentException("Invalid object ID hash supplied.");
        }
        $fileName = $this->getObjectsDirectory() . $hash;
        if (file_exists($fileName)) {
            $object = file_get_contents($fileName);
            if ($object === false) {
                if ($this->strict) {
                    throw new \RuntimeException("Unable to read cache object for ID hash $hash.");
                } else {
                    return null;
                }
            }
            $object = unserialize($object);
            $this->cacheObjects[$hash] = $object;
            return $object;
        } else {
            if ($this->strict) {
                throw new \RuntimeException("Requested cache object does not exist.");
            } else {
                return null;
            }
        }
    }

    protected function evictData($objectId)
    {
        if (!is_string($objectId)) {
            throw new \InvalidArgumentException("Invalid object ID supplied.");
        }
        if (!isset($this->cacheInfo["objects"][$objectId])) {
            throw new \RuntimeException("Cannot evict non-existent cache object.");
        }
        //TODO: finish this
    }

    public function __destruct()
    {
        foreach ($this->changed as $change) {
            $this->saveCacheObject($this->cacheInfo["objects"][$change]["store"]);
        }
        file_put_contents($this->cacheDirectory . self::CACHE_INFO_FILENAME, json_encode(array("cache" => $this->cacheInfo)));
    }
}
