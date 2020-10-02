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
use Sonata\NotificationBundle\Backend\QueueDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    /**
     * @var BackendInterface
     */
    private $backend;

    public function __construct(
        BackendInterface $backend
    ) {
        parent::__construct(null);
        $this->backend = $backend;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setName('sonata:notification:cleanup');
        $this->setDescription('Clean up backend message');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->write('<info>Starting ... </info>');

        $this->getBackend()->cleanup();

        $output->writeln('done!');
    }

    /**
     * @return BackendInterface
     */
    private function getBackend()
    {
        if ($this->backend instanceof QueueDispatcherInterface) {
            return $this->backend->getBackend(null);
        }

        return $this->backend;
    }
}
