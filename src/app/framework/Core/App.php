<?php

namespace MattyG\Framework\Core;

use \MattyG\Framework\Core\View\Manager as ViewManager;
use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;
use \Aura\Router\Router as Router;
use \Aura\Router\Route as Route;

class App
{
    const DIR_VAR = "var";

    /**
     * @var array
     */
    protected $version = array(
        "major" => 0,
        "minor" => 3,
        "patch" => 0,
    );

    /**
     * The base directory of the application, which should contain the config,
     * public, var and views directories.
     *
     * @var string
     */
    protected $baseDirectory;

    /**
     * The code pools in use by the application. By default, this will contain
     * "framework" and "user", and it should be in that order.
     *
     * @var array
     */
    protected $pools;

    /**
     * Contains the App object's reference to an instance of the Cache class.
     * Typically, this will be to use the file-based cache in the Core package.
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
     * @var MattyG\Framework\Core\View\Manager
     */
    protected $viewManager;

    /**
     * @param string $baseDirectory The base directory of the application.
     * @param array $pools The code pools in use by the application.
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
            $this->setCache(new Cache($this->getVarDirectory(), true));
            $this->setConfig(new Config($this->getBaseDirectory(), $pools, $this->getCache(), true));
            if ($this->getConfig()->getConfig("layout")) {
                $this->setViewManager(new ViewManager($this->getBaseDirectory(), $this->getConfig()));
            }
            if (($dbConfig = $this->getConfig()->getConfig("db")) && $dbConfig["active"] === true) {
                $this->setDB(DB::loader($dbConfig["type"], $dbConfig["database"], $dbConfig["hostname"], $dbConfig["username"], $dbConfig["password"]));
            }
        }
    }

    /**
     * Allows you to set the Cache dependency after construction of the App
     * object.
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
     * Allows you to set the Config dependency after construction of the App
     * object.
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
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Allows you to set the database dependency after construction of the App
     * object.
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
     * @return DB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Allows you to set the View Manager dependency after construction of the
     * App object.
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
     * @return MattyG\Framework\Core\View\Manager
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
        $route = $this->route($router, $request);
        $this->dispatch($request, $response, $route);
        $response->sendResponse();
    }

    /**
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
        $handler = new $handlerName($this->getConfig(), $this->getViewManager(), $request, $response, $routeName, $params);
        $return = $handler->dispatch($actionName);
        if ($return === false) {
            $this->dispatch($request, $response, false);
        }
        return $return;
    }

    /**
     * TODO: cache the routes on the router
     *
     * @param Router $router
     */
    protected function applyRoutes(Router $router)
    {
        $routesFile = $this->getBaseDirectory() . "app/routes.php";
        if (!file_exists($routesFile)) {
            throw new \RuntimeException("Cannot run application. No routes available.");
        }
        include($this->getBaseDirectory() . "app/routes.php");
    }

    /**
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
