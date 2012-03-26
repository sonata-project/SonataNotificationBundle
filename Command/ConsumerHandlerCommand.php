<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;

class ConsumerHandlerCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:notification:start');
        $this->setDescription('Listen for incoming messages');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Checking listeners');
        foreach($this->getDispatcher()->getListeners() as $type => $listeners) {
            $output->writeln(sprintf(" - %s", $type));
            foreach ($listeners as $listener) {
                if (!$listener[0] instanceof ConsumerInterface) {
                    throw new \RuntimeException(sprintf('The registered service does not implement the ConsumerInterface (class=%s', get_class($listener[0])));
                }

                $output->writeln(sprintf('   > %s', get_class($listener[0])));
            }
        }

        $output->writeln("<info>Starting the consumer handler</info>");
        foreach($this->getIterator() as $message) {
            $output->write(sprintf("<info>Handling message: </info> %s ... ", $message->getType()));
            try {
                $message->setStartedAt(new \DateTime());
                $this->getMessageManager()->save($message);

                $this->getDispatcher()->dispatch($message->getType(), new ConsumerEvent($message));

                $message->setCompletedAt(new \DateTime());
                $message->setState(MessageInterface::STATE_DONE);
                $this->getMessageManager()->save($message);

                $output->writeln("OK!");
            } catch (\Exception $e) {
                $message->setState(MessageInterface::STATE_ERROR);
                $this->getMessageManager()->save($message);

                $output->writeln(sprintf("KO! - %s", $e->getMessage()));
            }
        }
    }

    /**
     * @return \Sonata\NotificationBundle\Iterator\MessageIteratorInterface
     */
    private function getIterator()
    {
        return $this->getContainer()->get('sonata.notification.iterator');
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->getContainer()->get('sonata.notification.dispatcher');
    }

    /**
     * @return \Sonata\NotificationBundle\Model\MessageManagerInterface
     */
    private function getMessageManager()
    {
        return $this->getContainer()->get('sonata.notification.manager.message');
    }
}