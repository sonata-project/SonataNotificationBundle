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

namespace Sonata\NotificationBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Exception\InvalidParameterException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class InvalidParameterExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(InvalidParameterException::class);

        throw new InvalidParameterException();
    }
}
