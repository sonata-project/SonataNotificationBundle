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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListHandlerCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:notification:list-handler');
        $this->setDescription('List all consumers available');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>List of consumers available</info>');
        foreach ($this->getMetadata() as $type => $ids) {
            foreach ($ids as $id) {
                $output->writeln(sprintf('<info>%s</info> - <comment>%s</comment>', $type, $id));
            }
        }

        $output->writeln(' done!');

        return 0;
    }

    /**
     * @return array
     */
    private function getMetadata()
    {
        return $this->getContainer()->get('sonata.notification.consumer.metadata')->getInformations();
    }
}
