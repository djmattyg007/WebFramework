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
        $this->response->setBody("404 not found");
        return true;
    }
}

