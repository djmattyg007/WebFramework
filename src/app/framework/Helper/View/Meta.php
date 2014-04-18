<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper\HelperInterface as Helper;
use \MattyG\Framework\Core\Config as Config;
use \RyanNielson\Meta\Meta as MetaObject;

class Meta implements Helper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \RyanNielson\Meta\Meta
     */
    protected $metaObject;

    /**
     * @var array
     */
    protected $pageTitle;

    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName)
    {
        $this->config = $config;
        $this->metaObject = new MetaObject(true);
        $initialPageTitleSegment = $this->config->getConfig("site/page_title/title");
        $this->pageTitle = array($initialPageTitleSegment);
    }

    /**
     * @param string $segment
     * @return Meta
     */
    public function addPageTitleSegment($segment)
    {
        $this->pageTitle[] = $segment;
        return $this;
    }

    /**
     * @return array
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @return Meta
     */
    public function clearPageTitle()
    {
        $this->pageTitle = array();
        return $this;
    }

    /**
     * @param bool $includeContainer
     * @return string
     */
    public function getFormattedPageTitle($includeContainer = true)
    {
        $pageTitleSeparator = $this->config->getConfig("site/page_title/separator");
        $pageTitle = $this->pageTitle;
        if ($this->config->getConfig("site/page_title/reverse") === true) {
            $pageTitle = array_reverse($pageTitle);
        }
        $pageTitleString = implode($pageTitleSeparator, $pageTitle);
        if ($includeContainer === true) {
            $pageTitleString = "<title>$pageTitleString</title>";
        }
        return $pageTitleString;
    }

    /**
     * @param array $defaults The default meta attributes
     * @return string The meta tags
     */
    public function display(array $defaults = array())
    {
        $html = $this->metaObject->display($defaults);
        $html .= $this->getFormattedPageTitle(true) . "\n";
        return $html;
    }
}

