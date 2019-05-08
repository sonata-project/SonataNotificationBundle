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

namespace Sonata\NotificationBundle\Tests\Iterator;

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator;

/**
 * @covers \Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator
 */
class IteratorProxyMessageIteratorTest extends TestCase
{
    public function testIteratorProxiesIteratorMethods(): void
    {
        $content = [
            'foo',
            'bar',
        ];

        $actualIterator = $this->createMock('Iterator');
        $this->expectIterator($actualIterator, $content, true);

        $proxy = new IteratorProxyMessageIterator($actualIterator);
        foreach ($proxy as $eachKey => $eachEntry) {
            $this->assertNotNull($eachKey);
            $this->assertNotEmpty($eachEntry);
        }
    }

    /**
     * @see https://gist.github.com/2852498
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
                ->willReturn(true)
            ;

            $mock
                ->expects($this->at(++$counter))
                ->method('current')
                ->willReturn($value)
            ;

            if ($withKey) {
                $mock
                    ->expects($this->at(++$counter))
                    ->method('key')
                    ->willReturn($key)
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
            ->willReturn(false)
        ;

        return ++$counter;
    }
}
