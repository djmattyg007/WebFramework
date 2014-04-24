<?php

namespace MattyG\Framework\Helper\View;

use \MattyG\Framework\Core\Helper as Helper;
use \MattyG\Framework\Core\Config as Config;
use \RyanNielson\Meta\Meta as MetaObject;

class Meta implements Helper
{
    /**
     * @var \MattyG\Framework\Core\Config
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
     * @param \MattyG\Framework\Core\Config $config
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
     * Add a segment to the end of the list of segments used to make up the
     * page title.
     *
     * @param string $segment
     * @return Meta
     */
    public function addPageTitleSegment($segment)
    {
        $this->pageTitle[] = $segment;
        return $this;
    }

    /**
     * Returns all segments currently in the page title segment array.
     *
     * @return array
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Remove all segments currently in the page title segment array.
     *
     * @return Meta
     */
    public function clearPageTitle()
    {
        $this->pageTitle = array();
        return $this;
    }

    /**
     * Render the markup for the page title.
     *
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
     * Render all metadata tags for the <head> tag on a page.
     *
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

