<?php

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
use FOS\RestBundle\View\View;
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
        $messageManager->expects($this->once())->method('getPager')->will($this->returnValue([]));

        $paramFetcher = $this->createMock(ParamFetcher::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $this->assertEquals([], $this->createMessageController(null, $messageManager)->getMessagesAction($paramFetcher));
    }

    public function testPostMessageAction()
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())->method('save')->will($this->returnValue($message));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($message));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $message = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        $this->assertInstanceOf(MessageInterface::class, $message);
    }

    public function testPostMessageInvalidAction()
    {
        $message = $this->createMock(MessageInterface::class);

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->never())->method('save')->will($this->returnValue($message));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

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
            $messageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($message));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new MessageController($messageManager, $formFactory);
    }
}
