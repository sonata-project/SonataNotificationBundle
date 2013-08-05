<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Sonata\NotificationBundle\Tests\Iterator\MessageManagerMessageIterator;
use Sonata\NotificationBundle\Tests\Entity\MessageManagerMock;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class MessageManagerMessageIteratorTest
 * @package Sonata\NotificationBundle\Tests\Iterator
 */

class MessageManagerMessageIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testBufferize()
    {
        $iterator = new MessageManagerMessageIterator($this->getEmMock(), 0);

        $iterator->_bufferize();

        $this->assertEquals(10, count($iterator->getBuffer()));
    }

    public function testIterations()
    {
        $size = 10;

        $iterator = new MessageManagerMessageIterator($this->getEmMock(), 0);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        $size --;
        while (--$size >= 1) {
            $iterator->next();
        }

        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());
    }

    public function testLongForeach()
    {
        $iterator = new MessageManagerMessageIterator($this->getEmMock(), 500000, 2);

        $count = 0;

        foreach ($iterator as $message) {
            $count++;
            $this->assertNotNull($message);
            if($count > 20) {
                return;
            }
        }
    }

    protected function getEmMock()
    {
        $emMock  = $this->getMockBuilder('\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false)
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getMockBuilder('EntityRepository')
                ->disableOriginalConstructor()
                ->getMock()));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));
        return $emMock;  // it tooks 13 lines to achieve mock!
    }
}