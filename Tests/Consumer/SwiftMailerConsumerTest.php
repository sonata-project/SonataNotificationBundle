<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Consumer;

use Sonata\NotificationBundle\Consumer\SwiftMailerConsumer;
use Sonata\NotificationBundle\Model\Message;

/**
 * Tests the SwiftMailerConsumer.
 */
class SwiftMailerConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwiftMailerConsumer
     */
    private $consumer;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Initializes some dependencies used by tests.
     */
    protected function setUp()
    {
        $this->mailer = $this->getMockBuilder('Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumer = new SwiftMailerConsumer($this->mailer);
    }

    /**
     * Tests the sendEmail method.
     */
    public function testSendEmail()
    {
        $message = new Message();
        $message->setBody(array(
            'subject' => 'subject',
            'from' => array(
                'email' => 'from@mail.fr',
                'name' => 'nameFrom',
            ),
            'to' => array(
                'to1@mail.fr',
                'to2@mail.fr' => 'nameTo2',
            ),
            'replyTo' => array(
                'replyTo1@mail.fr',
                'replyTo2@mail.fr' => 'nameReplyTo2',
            ),
            'message' => array(
                'text' => 'message text',
                'html' => 'message html',
            ),
        ));

        $mail = $this->getMockBuilder('Swift_Message')->disableOriginalConstructor()->getMock();
        $mail->expects($this->once())->method('setSubject')->with($this->equalTo('subject'))->willReturnSelf();
        $mail->expects($this->once())->method('setFrom')->with($this->equalTo(array('from@mail.fr' => 'nameFrom')))->willReturnSelf();
        $mail->expects($this->once())->method('setTo')->with($this->equalTo(array('to1@mail.fr', 'to2@mail.fr' => 'nameTo2')))->willReturnSelf();
        $mail->expects($this->once())->method('setReplyTo')->with($this->equalTo(array('replyTo1@mail.fr', 'replyTo2@mail.fr' => 'nameReplyTo2')))->willReturnSelf();
        $mail->expects($this->exactly(2))
            ->method('addPart')
            ->withConsecutive(
                array($this->equalTo('message text'), $this->equalTo('text/plain')),
                array($this->equalTo('message html'), $this->equalTo('text/html'))
            )
            ->willReturnSelf();

        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($mail));

        $method = new \ReflectionMethod($this->consumer, 'sendEmail');
        $method->setAccessible(true);

        $method->invoke($this->consumer, $message);
    }
}
