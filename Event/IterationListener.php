<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Event;

/**
 * Listener for ConsumerHandlerCommand iterations event.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class IterationListener
 */
interface IterationListener
{
    /**
     * @param IterateEvent $event
     *
     * @return mixed
     */
    public function iterate(IterateEvent $event);
}
