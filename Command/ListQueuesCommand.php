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

use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class ListQueuesCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:notification:list-queues');
        $this->setDescription('List all queues available');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $backend = $this->getContainer()->get('sonata.notification.backend');

        if (!$backend instanceof QueueDispatcherInterface) {
            $output->writeln('The backend class <info>' . get_class($backend)  . '</info> does not provide multiple queues.');

            return;
        }

        $output->writeln('<info>List of queues available</info>');
        foreach ($backend->getQueues() as $queue) {
            $output->writeln(sprintf('queue: <info>%s</info> - routing_key: <info>%s</info>', $queue['queue'], $queue['routing_key']));
        }
    }
}
