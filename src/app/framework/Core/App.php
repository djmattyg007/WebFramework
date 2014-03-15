<?php

namespace MattyG\Framework\Core;

use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;

class App
{
    const DIRECTORY_CONFIG = "config";
    const DIRECTORY_VIEWS = "views";
    const DIRECTORY_VAR = "var";

    /**
     * @var array
     */
    protected $version = array(
        "major" => 0,
        "minor" => 1,
        "patch" => 0,
    );

    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @var array
     */
    protected $pools;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var ViewFactory
     */
    protected $viewFactory;

    /**
     * @param string $baseDirectory
     * @param array $pools
     * @param bool $initialise
     * @throws InvalidArgumentException
     */
    public function __construct($baseDirectory, array $pools, $initialise = true)
    {
        if (!is_string($baseDirectory)) {
            throw new InvalidArgumentException("Invalid parameter supplied for where to find base directory.");
        }
        $baseDirectory = realpath($baseDirectory);
        if (!file_exists($baseDirectory)) {
            throw new InvalidArgumentException("Base directory does not exist.");
        }
        $this->baseDirectory = rtrim($baseDirectory, "/") . "/";

        $this->pools = $pools;
        if ($initialise === true) {
            $this->setCache(new Cache($this->getVarDirectory(), true));
            $this->setConfig(new Config($this->getConfigDirectory(), $pools, $this->getCache(), true));
            if ($this->getConfig()->getConfig("layout")) {
                $this->setViewFactory(new ViewFactory($this->getViewsDirectory(), $this->getConfig()));
            }
            if (($dbConfig = $this->getConfig()->getConfig("db")) && $dbConfig["active"] === true) {
                $this->setDB(DB::loader($dbConfig["type"], $dbConfig["database"], $dbConfig["hostname"], $dbConfig["username"], $dbConfig["password"]));
            }
        }
    }

    /**
     *
     * @param Cache $cache
     * @return App
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Config $config
     * @return App
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param DB $db
     * @return App
     */
    public function setDB(DB $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return DB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * @param ViewFactory $viewFactory
     * @return App
     */
    public function setViewFactory(ViewFactory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        return $this;
    }

    /**
     * @return ViewFactory
     */
    public function getViewFactory()
    {
        return $this->viewFactory;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return string
     */
    public function getConfigDirectory()
    {
        return $this->getBaseDirectory() . self::DIRECTORY_CONFIG . "/";
    }

    /**
     * @return string
     */
    public function getViewsDirectory()
    {
        return $this->getBaseDirectory() . self::DIRECTORY_VIEWS . "/";
    }

    /**
     * @return string
     */
    public function getVarDirectory()
    {
        return $this->getBaseDirectory() . self::DIRECTORY_VAR . "/";
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function run(Request $request, Response $response)
    {
        $handler = new \MattyG\Framework\Handler\Home($this->getConfig(), $this->getViewFactory(), $request, $response, "home.view");
        $handler->dispatch("view");
        $response->sendResponse();
    }
}
