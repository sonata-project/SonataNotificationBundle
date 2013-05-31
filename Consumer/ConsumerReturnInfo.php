<?php

namespace Sonata\NotificationBundle\Consumer;


class ConsumerReturnInfo {

    /**
     * @var string
     */
    protected $returnMessage;

    /**
     * @param string $returnMessage
     */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage = $returnMessage;
    }

    /**
     * @return string
     */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }
}