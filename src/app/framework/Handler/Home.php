<?php

namespace MattyG\Framework\Handler;

use \MattyG\Framework\Core\Handler as AbstractHandler;

class Home extends AbstractHandler
{
    public function view()
    {
        $page = $this->prepareLayout();
        $this->response->setBody($page->render());
    }
}
