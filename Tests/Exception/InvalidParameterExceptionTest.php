<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\NotificationBundle\Tests\Exception;

use Sonata\NotificationBundle\Exception\InvalidParameterException;

/**
 * Class InvalidParameterExceptionTest
 *
 * @package Sonata\NotificationBundle\Tests\Exception
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class InvalidParameterExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Sonata\NotificationBundle\Exception\InvalidParameterException
     */
    public function testException()
    {
        throw new InvalidParameterException;
    }
}
