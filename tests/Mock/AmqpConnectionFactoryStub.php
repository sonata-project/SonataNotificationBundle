<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Mock;

use Interop\Amqp\AmqpConnectionFactory;
use Interop\Queue\PsrContext;

class AmqpConnectionFactoryStub implements AmqpConnectionFactory
{
    public static $config;
    public static $context;

    public function __construct($config)
    {
        static::$config = $config;
    }

    public function createContext(): PsrContext
    {
        if (null === static::$context) {
            throw new \Exception('The context MUST be set.');
        }

        return static::$context;
    }
}
