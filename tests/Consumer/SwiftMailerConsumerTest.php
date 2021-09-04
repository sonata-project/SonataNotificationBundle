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
    protected function setUp(): void
    {
        $this->mailer = $this->createMock('Swift_Mailer');
        $this->consumer = new SwiftMailerConsumer($this->mailer);
    }

    /**
     * Tests the sendEmail method.
     */
    public function testSendEmail(): void
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
            'returnPath' => [
                'email' => 'returnPath@mail.fr',
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
        $mail->expects(static::once())->method('setSubject')->with(static::equalTo('subject'))->willReturnSelf();
        $mail->expects(static::once())->method('setFrom')->with(static::equalTo(['from@mail.fr' => 'nameFrom']))->willReturnSelf();
        $mail->expects(static::once())->method('setTo')->with(static::equalTo(['to1@mail.fr', 'to2@mail.fr' => 'nameTo2']))->willReturnSelf();
        $mail->expects(static::once())->method('setReplyTo')->with(static::equalTo(['replyTo1@mail.fr', 'replyTo2@mail.fr' => 'nameReplyTo2']))->willReturnSelf();
        $mail->expects(static::once())->method('setReturnPath')->with(static::equalTo(['email' => 'returnPath@mail.fr']))->willReturnSelf();
        $mail->expects(static::once())
            ->method('setCc')
            ->with(static::equalTo(['cc1@mail.fr', 'cc2@mail.fr' => 'nameCc2']))
            ->willReturnSelf();
        $mail->expects(static::once())
            ->method('setBcc')
            ->with(static::equalTo(['bcc1@mail.fr', 'bcc2@mail.fr' => 'nameBcc2']))
            ->willReturnSelf();
        $mail->expects(static::exactly(2))
            ->method('addPart')
            ->withConsecutive(
                [static::equalTo('message text'), static::equalTo('text/plain')],
                [static::equalTo('message html'), static::equalTo('text/html')]
            )
            ->willReturnSelf();
        $mail->expects(static::once())
            ->method('attach')
            ->willReturnSelf();

        $this->mailer->expects(static::once())->method('createMessage')->willReturn($mail);

        $method = new \ReflectionMethod($this->consumer, 'sendEmail');
        $method->setAccessible(true);

        $method->invoke($this->consumer, $message);
    }
}
