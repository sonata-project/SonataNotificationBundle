<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator;

/**
 * @covers Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator
 */
class IteratorProxyMessageIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorProxiesIteratorMethods()
    {
        $content = array(
            'foo',
            'bar',
        );

        $actualIterator = $this->getMock('Iterator');
        $this->expectIterator($actualIterator, $content, true);

        $proxy = new IteratorProxyMessageIterator($actualIterator);
        foreach ($proxy as $eachKey => $eachEntry) {
            $this->assertNotNull($eachKey);
            $this->assertNotEmpty($eachEntry);
        }
    }

    /**
     * @link https://gist.github.com/2852498
     */
    public function expectIterator($mock, array $content, $withKey = false, $counter = 0)
    {
        $mock
            ->expects($this->at($counter))
            ->method('rewind')
        ;

        foreach ($content as $key => $value) {
            $mock
                ->expects($this->at(++$counter))
                ->method('valid')
                ->will($this->returnValue(true))
            ;

            $mock
                ->expects($this->at(++$counter))
                ->method('current')
                ->will($this->returnValue($value))
            ;

            if ($withKey) {
                $mock
                    ->expects($this->at(++$counter))
                    ->method('key')
                    ->will($this->returnValue($key))
                ;
            }

            $mock
                ->expects($this->at(++$counter))
                ->method('next')
            ;
        }

        $mock
            ->expects($this->at(++$counter))
            ->method('valid')
            ->will($this->returnValue(false))
        ;

        return ++$counter;
    }
}
