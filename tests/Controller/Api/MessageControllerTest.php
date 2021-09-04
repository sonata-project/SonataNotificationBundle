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

namespace Sonata\NotificationBundle\Tests\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use PHPUnit\Framework\TestCase;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\NotificationBundle\Controller\Api\MessageController;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageControllerTest extends TestCase
{
    public function testGetMessagesAction(): void
    {
        $messageManager = $this->createMock(MessageManagerInterface::class);
        $pager = $this->createStub(PagerInterface::class);
        $messageManager->expects(static::once())->method('getPager')->willReturn($pager);

        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher
            ->expects(static::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['page'],
                ['count'],
                ['orderBy']
            )->willReturnOnConsecutiveCalls(
                1,
                10,
                'ASC'
            );
        $paramFetcher->expects(static::once())->method('all')->willReturn([]);

        static::assertSame($pager, $this->createMessageController(null, $messageManager)->getMessagesAction($paramFetcher));
    }

    public function testPostMessageAction(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects(static::once())->method('save')->willReturn($message);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isSubmitted')->willReturn(true);
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($message);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $message = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        static::assertInstanceOf(MessageInterface::class, $message);
    }

    public function testPostMessageInvalidAction(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects(static::never())->method('save')->willReturn($message);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isSubmitted')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $form = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        static::assertInstanceOf(FormInterface::class, $form);
    }

    /**
     * @param $message
     * @param $messageManager
     * @param $formFactory
     */
    public function createMessageController($message = null, $messageManager = null, $formFactory = null): MessageController
    {
        if (null === $messageManager) {
            $messageManager = $this->createMock(SiteManagerInterface::class);
        }
        if (null !== $message) {
            $messageManager->expects(static::once())->method('findOneBy')->willReturn($message);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new MessageController($messageManager, $formFactory);
    }
}
