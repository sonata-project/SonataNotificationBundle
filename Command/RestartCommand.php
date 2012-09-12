<?php

namespace Sonata\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;

class RestartCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('sonata:notification:restart');
        $this->setDescription('Restart messages with erroneous statuses');
        $this->addOption('type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'List of messages types to restart');
        $this->addOption('max-attempts', null, InputOption::VALUE_REQUIRED, 'Maximum number of attempts', 5);
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

        if (0 == count($input->getOption('type'))) {
            throw new \Exception('You need to specify at least one message type.');
        }

        $messages = $this->getErroneousMessageSelector()->getMessages($input->getOption('type'), $input->getOption('max-attempts'));

        if (0 == count($messages)) {
            $output->writeln('Nothing to restart, bye.');

            return;
        }

        $manager = $this->getMessageManager();

        foreach ($messages as $message) {
            $output->writeln(sprintf('Reset Message <info>#%d</info> status', $message->getId()));

            $message->setRestartCount($message->getRestartCount() + 1);
            $message->setState(MessageInterface::STATE_OPEN);

            $manager->save($message);
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