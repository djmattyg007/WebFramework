<?php

namespace MattyG\Framework\Core;

use \MattyG\Framework\Core\View\Manager as ViewManager;
use \Aura\Di\Container as DIContainer;
use \MattyG\Framework\Core\DI\Factory as DIFactory;
use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;
use \Aura\Router\Router as Router;
use \Aura\Router\Route as Route;

class App
{
    const ROUTE_CACHE_ENTRY_NAME = "core_routes";

    const DIR_VAR = "var";

    /**
     * @var array
     */
    protected $version = array(
        "major" => 0,
        "minor" => 7,
        "patch" => 4,
    );

    /**
     * The base directory of the application, which should contain the config,
     * public, var and views directories.
     *
     * @var string
     */
    protected $baseDirectory;

    /**
     * The pools in use by the application. By default, this will contain
     * "framework" and "user", and it should be in that order.
     *
     * @var array
     */
    protected $pools;

    /**
     * Contains the App object's reference to the Dependency Injection
     * container. Holds all other services (Cache, Config, DB, View Manager)
     * as well as entity factories and helpers.
     *
     * @var \Aura\Di\Container
     */
    protected $diContainer;

    /**
     * Contains the App object's reference to an instance of the Cache class.
     * Typically, this will be to use the file-based cache in the framework
     * core.
     *
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
     * Contains the App object's reference to an instance of the View Manager
     * class. Its primary function is to create and render views. It can be
     * accessed from this object, and is passed to controllers upon
     * instantiation.
     *
     * @var \MattyG\Framework\Core\View\Manager
     */
    protected $viewManager;

    /**
     * @param string $baseDirectory The base directory of the application.
     * @param array $pools The pools in use by the application.
     * @param bool $initialise Whether or not to initialise the dependencies
     *      automatically. If false, you can set them manually later.
     * @throws \InvalidArgumentException
     */
    public function __construct($baseDirectory, array $pools, $initialise = true)
    {
        if (!is_string($baseDirectory)) {
            throw new \InvalidArgumentException("Invalid parameter supplied for where to find base directory.");
        }
        $baseDirectory = realpath($baseDirectory);
        if (!file_exists($baseDirectory)) {
            throw new \InvalidArgumentException("Base directory does not exist.");
        }
        $this->baseDirectory = rtrim($baseDirectory, "/") . "/";

        $this->pools = $pools;
        if ($initialise === true) {
            $this->initialise();
        }
    }

    /**
     * Initialises the primary services controlled by the App object.
     * This is the DI container, the Cache and the Config. If App finds layout
     * configuration, it will also instantiate a View Manager. If the App
     * finds database configuration, it will also instantiate a PDO container.
     * By default, it will also read the contents of di.php in all pools.
     *
     * @param bool $initialiseDIContainer Controls whether or not the contents
     *      of di.php in all pools are read in automatically.
     */
    public function initialise($initialiseDIContainer = true)
    {
        $diContainer = new DIContainer(new DIFactory());

        $cache = new Cache($this->getVarDirectory(), true);
        $this->setCache($cache);
        $diContainer->set("cache", $cache);

        $config = new Config($this->getBaseDirectory(), $this->pools, $cache, $diContainer, true);
        $this->setConfig($config);
        $diContainer->set("config", $config);

        if ($this->getConfig()->getConfig("layout")) {
            $viewManager = new ViewManager($this->getBaseDirectory(), array_reverse($this->pools), $config);
            $this->setViewManager($viewManager);
            $diContainer->set("viewManager", $viewManager);
        }

        if (($dbConfig = $this->getConfig()->getConfig("db")) && $dbConfig["active"] === true) {
            $db = DB::loader($dbConfig["adapter"], $dbConfig["name"], $dbConfig["host"], $dbConfig["user"], $dbConfig["pass"]);
            if ($db) {
                $db->setCache($this->getCache());
                $this->setDB($db);
                $diContainer->set("db", $db);
            }
        }

        if ($initialiseDIContainer === true) {
            $this->initialiseDIContainer($diContainer);
        }
        $this->setDIContainer($diContainer);
    }

    /**
     * Give the App object a different dependency injection container.
     *
     * @param \Aura\Di\Container $di
     * @return App
     */
    public function setDIContainer(DIContainer $di)
    {
        $this->diContainer = $di;
        return $this;
    }

    /**
     * Obtain the App object's current dependency injection container.
     *
     * @return \Aura\Di\Container $di
     */
    public function getDIContainer()
    {
        return $this->diContainer;
    }

    /**
     * Read the contents of di.php in all pools with the aim of initialising
     * the dependency injection container with various helpers and entities.
     *
     * @param \Aura\Di\Container $di
     * @return App
     */
    public function initialiseDIContainer(DIContainer $di)
    {
        foreach ($this->pools as $pool) {
            $routesFile = $this->getBaseDirectory() . "app/$pool/di.php";
            if (!file_exists($routesFile)) {
                continue;
            }
            include($routesFile);
        }
        return $this;
    }

    /**
     * Give the App object a different Cache instance.
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
     * Obtain the App object's current Cache object.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Give the App object a different Config instance.
     *
     * @param Config $config
     * @return App
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Obtain the App object's current Config object.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Give the App object a different PDO container.
     *
     * @param DB $db
     * @return App
     */
    public function setDB(DB $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Obtain the App object's current PDO container.
     *
     * @return DB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Give the App object a different view management class.
     *
     * @param MattyG\Framework\Core\View\Manager $viewManager
     * @return App
     */
    public function setViewManager(ViewManager $viewManager)
    {
        $this->viewManager = $viewManager;
        return $this;
    }

    /**
     * Obtain the App object's current view manager.
     *
     * @return \MattyG\Framework\Core\View\Manager
     */
    public function getViewManager()
    {
        return $this->viewManager;
    }

    /**
     * Returns the base directory of the application.
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Returns the directory designed to contain free writeable storage for the
     * application.
     *
     * @return string
     */
    public function getVarDirectory()
    {
        return $this->getBaseDirectory() . self::DIR_VAR . "/";
    }

    /**
     * Route, dispatch and send!
     *
     * @param Request $request
     * @param Response $response
     * @param Router $router
     */
    public function run(Request $request, Response $response, Router $router)
    {
        $this->getConfig()->setRouterObject($router);
        $this->diContainer->set("router", $router);
        $route = $this->route($router, $request);
        $this->dispatch($request, $response, $route);
        $response->sendResponse();
    }

    /**
     * Route!
     *
     * @param Router $router
     * @param Request $request
     * @return Route|false
     */
    protected function route(Router $router, Request $request)
    {
        $this->applyRoutes($router);
        $uriPath = "/" . trim(parse_url($request->getRequestUri(), PHP_URL_PATH), "/");
        return $router->match($uriPath, $request->getServerVar());
    }

    /**
     * Dispatch!
     *
     * @param Request $request
     * @param Response $resonse
     * @param Route|false $route
     * @return bool
     */
    protected function dispatch(Request $request, Response $response, $route)
    {
        if ($route instanceof Route) {
            $routeName = $route->name;
            $handlerName = $this->prepareHandlerName($route->params["controller"]);
            $actionName = $route->params["action"];
            $params = $route->params;
        } else {
            $routeName = "core.404";
            $handlerName = $this->prepareHandlerName("core");
            $actionName = "four04";
            $params = array();
        }
        /** @var $handler \MattyG\Framework\Core\Handler */
        $handler = new $handlerName($this->getConfig(), $this->getViewManager(), $this->getDIContainer(), $request, $response, $routeName, $params);
        $return = $handler->dispatch($actionName);
        if ($return === false) {
            $this->dispatch($request, $response, false);
        }
        return $return;
    }

    /**
     * Gets the list of routes defined in routes.php in all pools and adds
     * them to the supplied Router object.
     * The routes will be loaded from the cache if available.
     *
     * @param Router $router
     * @return void
     */
    protected function applyRoutes(Router $router)
    {
        $cachedRoutes = $this->getCache()->loadData(self::ROUTE_CACHE_ENTRY_NAME, null);
        if ($cachedRoutes) {
            $router->setRoutes($cachedRoutes);
            return;
        }

        foreach ($this->pools as $pool) {
            $routesFile = $this->getBaseDirectory() . "app/$pool/routes.php";
            if (!file_exists($routesFile)) {
                continue;
            }
            include($routesFile);
        }
        if ($router->count() == 0) {
            throw new \RuntimeException("Cannot run application. No routes available.");
        }

        $this->cache->saveData(self::ROUTE_CACHE_ENTRY_NAME, $router->getRoutes(), time() + 3600);
    }

    /**
     * Takes a handler name of the form "group1.group2.handler" and returns
     * the fully-qualified class name of the handler.
     * The fully qualified class name of "group1.group2.handler" would be
     * \MattyG\Framework\Handler\Group1\Group2\Handler
     *
     * @param string $name
     * @return string
     */
    protected function prepareHandlerName($name)
    {
        $name = str_replace(".", " ", $name);
        $name = ucwords($name);
        return "\\MattyG\\Framework\\Handler\\" . str_replace(" ", "\\", $name);
    }
}
