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

use Sonata\NotificationBundle\Consumer\Metadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListHandlerCommand extends Command
{
    /**
     * @var Metadata
     */
    private $metadata;

    public function __construct(Metadata $metadata)
    {
        parent::__construct(null);

        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setName('sonata:notification:list-handler');
        $this->setDescription('List all consumers available');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>List of consumers available</info>');
        foreach ($this->metadata->getInformations() as $type => $ids) {
            foreach ($ids as $id) {
                $output->writeln(sprintf('<info>%s</info> - <comment>%s</comment>', $type, $id));
            }
        }

        $output->writeln(' done!');
    }
}
