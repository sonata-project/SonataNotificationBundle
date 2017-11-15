<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class MessageManagerMessageIteratorTest extends TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    protected function setUp()
    {
        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    public function testBufferize()
    {
        $iterator = new MessageManagerMessageIterator($this->registry, 0);

        $iterator->_bufferize();

        $this->assertEquals(10, count($iterator->getBuffer()));
    }

    public function testIterations()
    {
        $size = 10;

        $iterator = new MessageManagerMessageIterator($this->registry, 0);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        --$size;
        while (--$size >= 1) {
            $iterator->next();
        }

        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());
    }

    public function testLongForeach()
    {
        $iterator = new MessageManagerMessageIterator($this->registry, 500000, 2);

        $count = 0;

        foreach ($iterator as $message) {
            ++$count;
            $this->assertNotNull($message);
            if ($count > 20) {
                return;
            }
        }
    }
}
