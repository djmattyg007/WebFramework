<?php

namespace MattyG\Framework\Handler;

use \MattyG\Framework\Core\Handler as AbstractHandler;

class Home extends AbstractHandler
{
    /**
     * @return bool
     */
    public function indexAction()
    {
        /** @var $metaHelper \MattyG\Framework\Helper\View\Meta */
        $metaHelper = $this->viewManager->getHelper("meta");
        $metaHelper->addPageTitleSegment("Home");
        $metaHelper->set($this->getRouteMetaTags());

        $page = $this->prepareLayout();
        $this->response->setBody($page->render());
        return true;
    }
}
