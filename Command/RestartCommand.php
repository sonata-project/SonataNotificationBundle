<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Command;

use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Iterator\ErroneousMessageIterator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\NotificationBundle\Model\MessageInterface;

class RestartCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('sonata:notification:restart');
        $this->setDescription('Restart messages with erroneous statuses, only for doctrine backends');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of messages types to restart');
        $this->addOption('max-attempts', null, InputOption::VALUE_REQUIRED, 'Maximum number of attempts', 6);
        $this->addOption('attempt-delay', null, InputOption::VALUE_OPTIONAL, 'Min seconds between two attempts', 10);
        $this->addOption('pulling', null, InputOption::VALUE_NONE, 'Run the command as an infinite pulling loop');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting... </info>');

        if (!is_numeric($input->getOption('max-attempts'))) {
            throw new \Exception('Option "max-attempts" is invalid (integer value needed).');
        }

        $pullMode = $input->getOption('pulling');
        $manager = $this->getMessageManager();

        if ($pullMode) {
            $messages = new ErroneousMessageIterator(
                $manager,
                $input->getOption('type'),
                null,
                null,
                $input->getOption('max-attempts'),
                $input->getOption('attempt-delay'));
        } else {
            $messages = $this->getErroneousMessageSelector()->getMessages($input->getOption('type'), $input->getOption('max-attempts'));
        }

        if (0 == count($messages)) {
            $output->writeln('Nothing to restart, bye.');

            return;
        }

        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        foreach ($messages as $message) {
            if ($message->isOpen() || $message->isRunning()) {
                continue;
            }

            $id = $message->getId();
            $message->setState(MessageInterface::STATE_CANCELLED);
            $manager->save($message);

            $count = $message->getRestartCount();

            $message = clone $message;
            $message->setRestartCount($count + 1);

            $this->getContainer()->get('sonata.notification.backend')->publish($message);

            $output->writeln(sprintf('Reset Message %s <info>#%d</info>, new id %d. Attempt #%d', $message->getType(), $id, $message->getId(), $message->getRestartCount()));

            if ($pullMode) {
                $eventDispatcher->dispatch(IterateEvent::EVENT_NAME, new IterateEvent($message, null, $message));
            }
        }

        $output->writeln('<info>Done!</info>');
    }

    /**
     * Return the errorneous message selector service.
     *
     * @return \Sonata\NotificationBundle\Selector\ErroneousMessagesSelector
     */
    protected function getErroneousMessageSelector()
    {
        return $this->getContainer()->get('sonata.notification.erroneous_messages_selector');
    }

    /**
     * @return \Sonata\NotificationBundle\Model\MessageManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->getContainer()->get('sonata.notification.manager.message');
    }
}