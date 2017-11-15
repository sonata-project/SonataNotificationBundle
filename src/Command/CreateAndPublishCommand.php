<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAndPublishCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('sonata:notification:create-and-publish')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of the notification')
            ->addArgument('body', InputArgument::REQUIRED, 'Body of the notification (json)');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $body = json_decode($input->getArgument('body'), true);

        if (null === $body) {
            throw new \InvalidArgumentException('Body does not contain valid json.');
        }

        $this->getContainer()
            ->get('sonata.notification.backend')
            ->createAndPublish($type, $body)
        ;

        $output->writeln('<info>Done !</info>');
    }
}
