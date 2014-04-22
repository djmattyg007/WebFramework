<?php

namespace MattyG\Framework\Core\View;

class View
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var Manager
     */
    protected $viewManager;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var bool
     */
    protected $directOutput;

    /**
     * @var array
     */
    protected $vars;

    /**
     * @var array
     */
    protected $childViews;

    /**
     * @param string $filename
     * @param Manager $viewManager
     * @param array $children
     */
    public function __construct($fileName, Manager $viewManager, array $children = array(), $directOutput = false)
    {
        $this->fileName = $fileName;
        $this->viewManager = $viewManager;
        $this->children = $children;
        $this->directOutput = $directOutput;
        $this->vars = array();
        $this->childViews = array();
    }

    /**
     * If set to true, output buffering will not be used when rendering this
     * view. If false (the default), output buffering will be used.
     *
     * @param bool $directOutput
     * @return View
     */
    public function setDirectOutput($directOutput)
    {
        $this->directOutput = $directOutput;
        return $this;
    }

    /**
     * Check whether or not output buffering should be used when rendering this
     * view.
     *
     * @return bool
     */
    public function getDirectOutput()
    {
        return $this->directOutput;
    }

    /**
     * @param array $vars
     * @return View
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return View
     */
    public function addVar($name, $value)
    {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function delVar($name)
    {
        if (isset($this->vars[$name])) {
            $value = $this->vars[$name];
            unset($this->vars[$name]);
            return $value;
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getVar($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        if (file_exists($this->fileName) && is_readable($this->fileName)) {
            extract($this->getVars(), EXTR_SKIP);
            if (!$this->directOutput) {
                ob_start();
            }
            include($this->fileName);
            if (!$this->directOutput) {
                return ob_get_clean();
            }
        }
        return "";
    }

    /**
     * @param string $name
     * @return View
     */
    public function getChild($name, $rememberChild = true)
    {
        if (isset($this->children[$name])) {
            if (!isset($this->childViews[$name])) {
                $this->childViews[$name] = $this->viewManager->newView($this->children[$name], false);
            }
            return $this->childViews[$name];
        } else {
            return null;
        }
    }

    /**
     * Convenience method so that view files don't need to manually check that
     * a child exists themselves.
     *
     * @param string $name
     * @return string
     */
    public function renderChild($name)
    {
        if ($child = $this->getChild($name)) {
            return $child->render();
        } else {
            return "";
        }
    }
}
