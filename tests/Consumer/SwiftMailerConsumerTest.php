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

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Consumer\SwiftMailerConsumer;
use Sonata\NotificationBundle\Model\Message;

/**
 * Tests the SwiftMailerConsumer.
 */
class SwiftMailerConsumerTest extends TestCase
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
        $this->mailer = $this->createMock('Swift_Mailer');
        $this->consumer = new SwiftMailerConsumer($this->mailer);
    }

    /**
     * Tests the sendEmail method.
     */
    public function testSendEmail()
    {
        $message = new Message();
        $message->setBody([
            'subject' => 'subject',
            'from' => [
                'email' => 'from@mail.fr',
                'name' => 'nameFrom',
            ],
            'to' => [
                'to1@mail.fr',
                'to2@mail.fr' => 'nameTo2',
            ],
            'replyTo' => [
                'replyTo1@mail.fr',
                'replyTo2@mail.fr' => 'nameReplyTo2',
            ],
            'cc' => [
                'cc1@mail.fr',
                'cc2@mail.fr' => 'nameCc2',
            ],
            'bcc' => [
                'bcc1@mail.fr',
                'bcc2@mail.fr' => 'nameBcc2',
            ],
            'message' => [
                'text' => 'message text',
                'html' => 'message html',
            ],
            'attachment' => [
                'file' => 'path to file',
                'name' => 'file name',
            ],
        ]);

        $mail = $this->createMock('Swift_Message');
        $mail->expects($this->once())->method('setSubject')->with($this->equalTo('subject'))->willReturnSelf();
        $mail->expects($this->once())->method('setFrom')->with($this->equalTo(['from@mail.fr' => 'nameFrom']))->willReturnSelf();
        $mail->expects($this->once())->method('setTo')->with($this->equalTo(['to1@mail.fr', 'to2@mail.fr' => 'nameTo2']))->willReturnSelf();
        $mail->expects($this->once())->method('setReplyTo')->with($this->equalTo(['replyTo1@mail.fr', 'replyTo2@mail.fr' => 'nameReplyTo2']))->willReturnSelf();
        $mail->expects($this->once())
            ->method('setCc')
            ->with($this->equalTo(['cc1@mail.fr', 'cc2@mail.fr' => 'nameCc2']))
            ->willReturnSelf();
        $mail->expects($this->once())
            ->method('setBcc')
            ->with($this->equalTo(['bcc1@mail.fr', 'bcc2@mail.fr' => 'nameBcc2']))
            ->willReturnSelf();
        $mail->expects($this->exactly(2))
            ->method('addPart')
            ->withConsecutive(
                [$this->equalTo('message text'), $this->equalTo('text/plain')],
                [$this->equalTo('message html'), $this->equalTo('text/html')]
            )
            ->willReturnSelf();
        $mail->expects($this->once())
            ->method('attach')
            ->willReturnSelf();

        $this->mailer->expects($this->once())->method('createMessage')->will($this->returnValue($mail));

        $method = new \ReflectionMethod($this->consumer, 'sendEmail');
        $method->setAccessible(true);

        $method->invoke($this->consumer, $message);
    }
}
