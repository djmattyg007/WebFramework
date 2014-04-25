<?php

namespace MattyG\Framework\Core;

use \MattyG\Framework\Core\View\Manager as ViewManager;
use \Aura\Di\Container as DIContainer;
use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;

abstract class Handler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \MattyG\Framework\Core\View\Manager
     */
    protected $viewManager;

    /**
     * @var \Aura\Di\Container
     */
    protected $di;

    /**
     * @var \MattyG\Http\Request
     */
    protected $request;

    /**
     * @var \MattyG\Http\Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @param Config $config
     * @param \MattyG\Framework\Core\View\Manager $viewManager
     * @param \Aura\Di\Container $diContainer
     * @param Request $request
     * @param Response $response
     * @param string $routeName
     * @param array $params
     */
    public function __construct(Config $config, ViewManager $viewManager, DIContainer $diContainer, Request $request, Response $response, $routeName, array $params)
    {
        $this->config = $config;
        $this->viewManager = $viewManager;
        $this->di = $diContainer;
        $this->request = $request;
        $this->response = $response;
        $this->routeName = $routeName;
        $this->params = $params;
    }

    /**
     * Perform tasks before the controller action is dispatched.
     *
     * @return bool
     */
    protected function preDispatch()
    {
        return true;
    }

    /**
     * Perform tasks after the controller action has been dispatched.
     *
     * @return bool
     */
    protected function postDispatch()
    {
        return true;
    }

    /**
     * Dispatch the desired action on this controller object.
     * Action methods must end in "Action". This is to avoid conflicts with
     * other functions in the controller.
     * Returning false from this function implies a "not found".
     *
     * @param string $action
     * @return bool
     */
    public function dispatch($action)
    {
        $this->actionName = $action . "Action";

        if (!$this->preDispatch()) {
            return false;
        }

        if (!method_exists($this, $this->actionName)) {
            return false;
        }

        if (!$this->{$this->actionName}()) {
            return false;
        }

        return $this->postDispatch();
    }

    /**
     * Prepare the layout for the current controller action, and return a View
     * object for the root page defined for the loaded layout.
     *
     * @return \MattyG\Framework\Core\View\View
     */
    protected function prepareLayout()
    {
        return $this->viewManager->newRootView($this->routeName);
    }

    /**
     * @return array
     */
    protected function getRouteMetaTags()
    {
        $defaults = $this->config->getConfig("site/meta");
        $route = $this->config->getConfig("routes/*/name=" . $this->routeName . "/meta");
        return array_merge($defaults, $route);
    }
}
