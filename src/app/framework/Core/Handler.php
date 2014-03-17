<?php

namespace MattyG\Framework\Core;

use \MattyG\Http\Request as Request;
use \MattyG\Http\Response as Response;

abstract class Handler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ViewFactory
     */
    protected $viewFactory;

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
     * @param ViewFactory $viewFactory
     * @param Request $request
     * @param Response $response
     * @param string $routeName
     */
    public function __construct(Config $config, ViewFactory $viewFactory, Request $request, Response $response, $routeName)
    {
        $this->config = $config;
        $this->viewFactory = $viewFactory;
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
        return $this->viewFactory->newRootView($this->routeName);
    }
}

