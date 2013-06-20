<?php

namespace Sonata\NotificationBundle\Consumer;


/**
 *
 * Return informations for comsumers
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class ConsumerReturnInfo
 * @package Sonata\NotificationBundle\Consumer
 */
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