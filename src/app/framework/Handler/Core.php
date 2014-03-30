<?php

namespace MattyG\Framework\Handler;

use \MattyG\Framework\Core\Handler as AbstractHandler;

class Core extends AbstractHandler
{
    /**
     * @return bool
     */
    public function four04Action()
    {
        $this->response->setResponseCode(404);
        $page = $this->prepareLayout();
        $this->response->setBody($page->render());
        return true;
    }
}

