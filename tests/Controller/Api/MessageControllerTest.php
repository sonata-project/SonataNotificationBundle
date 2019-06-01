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

use FOS\RestBundle\Request\ParamFetcher;
use PHPUnit\Framework\TestCase;
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
    public function testGetMessagesAction()
    {
        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())->method('getPager')->willReturn([]);

        $paramFetcher = $this->createMock(ParamFetcher::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $this->assertSame([], $this->createMessageController(null, $messageManager)->getMessagesAction($paramFetcher));
    }

    public function testPostMessageAction()
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())->method('save')->willReturn($message);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($message);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $message = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        $this->assertInstanceOf(MessageInterface::class, $message);
    }

    public function testPostMessageInvalidAction()
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->never())->method('save')->willReturn($message);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $form = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        $this->assertInstanceOf(FormInterface::class, $form);
    }

    /**
     * @param $message
     * @param $messageManager
     * @param $formFactory
     *
     * @return MessageController
     */
    public function createMessageController($message = null, $messageManager = null, $formFactory = null)
    {
        if (null === $messageManager) {
            $messageManager = $this->createMock(SiteManagerInterface::class);
        }
        if (null !== $message) {
            $messageManager->expects($this->once())->method('findOneBy')->willReturn($message);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new MessageController($messageManager, $formFactory);
    }
}
