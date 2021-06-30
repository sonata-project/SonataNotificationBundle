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

namespace Sonata\NotificationBundle\Command;

use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Iterator\ErroneousMessageIterator;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Sonata\NotificationBundle\Selector\ErroneousMessagesSelector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @final since sonata-project/notification-bundle 3.13
 */
class RestartCommand extends Command
{
    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @var ErroneousMessagesSelector
     */
    private $erroneousMessagesSelector;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    public function __construct(BackendInterface $backend, ErroneousMessagesSelector $erroneousMessagesSelector, EventDispatcherInterface $eventDispatcher, MessageManagerInterface $messageManager)
    {
        parent::__construct(null);

        $this->backend = $backend;
        $this->erroneousMessagesSelector = $erroneousMessagesSelector;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageManager = $messageManager;
    }

    public function configure(): void
    {
        $this->setName('sonata:notification:restart');
        $this->setDescription('Restart messages with erroneous statuses, only for doctrine backends');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of messages types to restart (separate multiple types with a space)');
        $this->addOption('max-attempts', null, InputOption::VALUE_REQUIRED, 'Maximum number of attempts', 6);
        $this->addOption('attempt-delay', null, InputOption::VALUE_OPTIONAL, 'Min seconds between two attempts', 10);
        $this->addOption('pulling', null, InputOption::VALUE_NONE, 'Run the command as an infinite pulling loop');
        $this->addOption('pause', null, InputOption::VALUE_OPTIONAL, 'Seconds between each data pull (used only when pulling option is set)', 500000);
        $this->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, 'Number of message to process on each pull (used only when pulling option is set)', 10);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting... </info>');

        if (!is_numeric($input->getOption('max-attempts'))) {
            throw new \Exception('Option "max-attempts" is invalid (integer value needed).');
        }

        $pullMode = $input->getOption('pulling');

        if ($pullMode) {
            $messages = new ErroneousMessageIterator(
                $this->messageManager,
                $input->getOption('type'),
                $input->getOption('pause'),
                $input->getOption('batch-size'),
                $input->getOption('max-attempts'),
                $input->getOption('attempt-delay')
            );
        } else {
            $messages = $this->erroneousMessagesSelector->getMessages(
                $input->getOption('type'),
                $input->getOption('max-attempts')
            );

            /*
             * Check messages count only for not pulling mode
             * to avoid PHP warning message
             * since ErroneousMessageIterator does not implement Countable.
             */
            if (0 === \count($messages)) {
                $output->writeln('Nothing to restart, bye.');

                return 0;
            }
        }

        foreach ($messages as $message) {
            $id = $message->getId();

            $newMessage = $this->messageManager->restart($message);

            $this->backend->publish($newMessage);

            $output->writeln(sprintf(
                'Reset Message %s <info>#%d</info>, new id %d. Attempt #%d',
                $newMessage->getType(),
                $id,
                $newMessage->getId(),
                $newMessage->getRestartCount()
            ));

            if ($pullMode) {
                $this->eventDispatcher->dispatch(new IterateEvent($messages, null, $newMessage), IterateEvent::EVENT_NAME);
            }
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
