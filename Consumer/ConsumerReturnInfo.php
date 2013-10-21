<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
class ConsumerReturnInfo
{

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