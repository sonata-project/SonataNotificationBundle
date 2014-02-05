<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\NotificationBundle\Tests\Controller\Api;

use Sonata\NotificationBundle\Controller\Api\MessageController;


/**
 * Class MessageControllerTest
 *
 * @package Sonata\NotificationBundle\Tests\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMessagesAction()
    {
        $messageManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $messageManager->expects($this->once())->method('findBy');

        $paramFetcher = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue(array()));

        $controller = new MessageController($messageManager);
        $controller->getMessagesAction($paramFetcher);
    }
}
