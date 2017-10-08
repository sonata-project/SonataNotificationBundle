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

use Sonata\NotificationBundle\Controller\Api\MessageController;
use Sonata\NotificationBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageControllerTest extends PHPUnit_Framework_TestCase
{
    public function testGetMessagesAction()
    {
        $messageManager = $this->createMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $messageManager->expects($this->once())->method('getPager')->will($this->returnValue([]));

        $paramFetcher = $this->createMock('FOS\RestBundle\Request\ParamFetcher');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $this->assertEquals([], $this->createMessageController(null, $messageManager)->getMessagesAction($paramFetcher));
    }

    public function testPostMessageAction()
    {
        $message = $this->createMock('Sonata\NotificationBundle\Model\MessageInterface');

        $messageManager = $this->createMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $messageManager->expects($this->once())->method('save')->will($this->returnValue($message));

        $form = $this->createMock('Symfony\Component\Form\Form');
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($message));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostMessageInvalidAction()
    {
        $message = $this->createMock('Sonata\NotificationBundle\Model\MessageInterface');

        $messageManager = $this->createMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $messageManager->expects($this->never())->method('save')->will($this->returnValue($message));

        $form = $this->createMock('Symfony\Component\Form\Form');
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createMessageController(null, $messageManager, $formFactory)->postMessageAction(new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
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
            $messageManager = $this->createMock('Sonata\PageBundle\Model\SiteManagerInterface');
        }
        if (null !== $message) {
            $messageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($message));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        }

        return new MessageController($messageManager, $formFactory);
    }
}
