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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\NotificationBundle\Consumer\ConsumerInterface;

class ConsumerHandlerCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:notification:start');
        $this->setDescription('Listen for incoming messages');
        $this->addOption('iteration', 'i', InputOption::VALUE_OPTIONAL ,'Only run n iterations before exiting', false);
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Use a specific backed based on a message type', null);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = new \DateTime();
        $now = $startDate->format('r');
        $output->writeln(sprintf('[%s] <info>Checking listeners</info>', $now));
        foreach ($this->getDispatcher()->getListeners() as $type => $listeners) {
            $output->writeln(sprintf(" - %s", $type));
            foreach ($listeners as $listener) {
                if (!$listener[0] instanceof ConsumerInterface) {
                    throw new \RuntimeException(sprintf('The registered service does not implement the ConsumerInterface (class=%s', get_class($listener[0])));
                }

                $output->writeln(sprintf('   > %s', get_class($listener[0])));
            }
        }

        $type = $input->getOption('type');
        $backend = $this->getBackend($type);

        $output->writeln("");
        $output->write(sprintf('[%s] <info>Initialize backend</info> ...', $now));

        // initialize the backend
        $backend->initialize();
        $output->writeln(" done!");

        if ($type === null) {
            $output->writeln(sprintf("[%s] <info>Starting the backend handler</info> - %s", $now, get_class($backend)));
        } else {
            $output->writeln(sprintf("[%s] <info>Starting the backend handler</info> - %s (type: %s)", $now, get_class($backend), $type));
        }

        $dispatcher = $this->getDispatcher();

        $startMemoryUsage = memory_get_usage(true);
        $i = 0;
        foreach ($backend->getIterator() as $message) {
            $i++;
            if (!$message->getType()) {
                $output->write("<error>Skipping : no type defined </error>");
                continue;
            }

            $date = new \DateTime();
            $output->write(sprintf("[%s] <info>%s</info> : ", $date->format('r'), $message->getType(), $i));
            $memoryUsage = memory_get_usage(true);
            try {

                $start = microtime(true);
                $backend->handle($message, $dispatcher);

                $currentMemory = memory_get_usage(true);

                $output->writeln(sprintf("<comment>OK! </comment> - %0.04fs, %ss, %s, %s - %s = %s, %0.02f%%",
                    microtime(true) - $start,
                    $date->format('U') - $message->getCreatedAt()->format('U'),
                    $this->formatMemory($currentMemory - $memoryUsage),
                    $this->formatMemory($currentMemory),
                    $this->formatMemory($startMemoryUsage),
                    $this->formatMemory($currentMemory - $startMemoryUsage),
                    ($currentMemory - $startMemoryUsage) / $startMemoryUsage * 100
                ));

            } catch (\Exception $e) {
                if ($e instanceof \Sonata\NotificationBundle\Exception\HandlingException) {
                    $output->writeln(sprintf("<error>KO! - %s</error>", $e->getPrevious()->getMessage()));
                } else {
                    $output->writeln(sprintf("<error>KO! - %s</error>", $e->getMessage()));
                }
            }

            $this->optimize();

            if ($input->getOption('iteration') && $i >= (int) $input->getOption('iteration')) {
                $output->writeln('End of iteration cycle');

                return;
            }
        }
    }

    private function optimize()
    {
        if ($this->getContainer()->has('doctrine')) {
            $this->getContainer()->get('doctrine')->getEntityManager()->getUnitOfWork()->clear();
        }
    }

    /**
     * @param $memory
     * @return string
     */
    private function formatMemory($memory)
    {
        if ($memory < 1024) {
            return $memory."b";
        } elseif ($memory < 1048576) {
            return round($memory / 1024, 2)."Kb";
        } else {
            return round($memory / 1048576, 2)."Mb";
        }
    }

    /**
     * @param  string                                              $type
     * @return \Sonata\NotificationBundle\Backend\BackendInterface
     */
    private function getBackend($type = null)
    {
        $backend = $this->getContainer()->get('sonata.notification.backend');

        if ($backend instanceof QueueDispatcherInterface) {
            return $backend->getBackend($type);
        }

        return $backend;
    }

    /**
     * @param  string            $type
     * @throws \RuntimeException
     */
    protected function throwTypeNotFoundException($type, $backend)
    {
        throw new \RuntimeException("The requested backend for the type '" . $type . " 'does not exist. \nMake sure the backend '" .
                get_class($backend) . "' \nsupports multiple queues and the routing_key is defined. (Currently rabbitmq only)");
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
