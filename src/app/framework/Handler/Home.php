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
        $meta = $this->viewManager->getHelper("meta");
        $meta->addPageTitleSegment("Home");
        $page = $this->prepareLayout();
        $this->response->setBody($page->render());
        return true;
    }
}
