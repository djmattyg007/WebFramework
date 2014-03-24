<?php

namespace MattyG\Framework\Core;

use \MattyG\Framework\Core\View\Manager as ViewManager;
use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;

abstract class Handler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MattyG\Framework\Core\View\Manager
     */
    protected $viewManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @param Config $config
     * @param MattyG\Framework\Core\View\Manager $viewManager
     * @param Request $request
     * @param Response $response
     * @param string $routeName
     */
    public function __construct(Config $config, ViewManager $viewManager, Request $request, Response $response, $routeName)
    {
        $this->config = $config;
        $this->viewManager = $viewManager;
        $this->request = $request;
        $this->response = $response;
        $this->routeName = $routeName;
    }

    /**
     * Perform a task or tasks before the controller action is dispatched.
     *
     * @return Handler
     */
    protected function preDispatch()
    {
        return $this;
    }

    /**
     * Perform a task or tasks after the controller action has been dispatched.
     *
     * @return Handler
     */
    protected function postDispatch()
    {
        return $this;
    }

    /**
     * @param string $action
     */
    public function dispatch($action)
    {
        $this->preDispatch();

        if (method_exists($this, $action)) {
            $this->$action();
        }

        $this->postDispatch();
    }

    /**
     * Prepare the layout for the current controller action, and return a View
     * object for the root page for the loaded layout.
     *
     * @return View
     */
    protected function prepareLayout()
    {
        return $this->viewManager->newRootView($this->routeName);
    }
}

