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

use Sonata\NotificationBundle\Model\MessageInterface;

interface ConsumerEventInterface
{
    /**
     * @return MessageInterface
     */
    public function getMessage();
}
