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

namespace Sonata\NotificationBundle\Selector;

use Doctrine\Persistence\ManagerRegistry;
use Sonata\NotificationBundle\Model\MessageInterface;

/**
 * @final since sonata-project/notification-bundle 3.x
 */
class ErroneousMessagesSelector
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param string $class
     */
    public function __construct(ManagerRegistry $registry, $class)
    {
        $this->registry = $registry;
        $this->class = $class;
    }

    /**
     * Retrieve messages with given type(s) and restrict to max attempts count.
     *
     * @param int $maxAttempts
     *
     * @return array
     */
    public function getMessages(array $types, $maxAttempts = 5)
    {
        $query = $this->registry->getManagerForClass($this->class)->getRepository($this->class)
            ->createQueryBuilder('m')
            ->where('m.state = :erroneousState')
            ->andWhere('m.restartCount < :maxAttempts');

        $parameters = [
            'erroneousState' => MessageInterface::STATE_ERROR,
            'maxAttempts' => $maxAttempts,
        ];

        if (\count($types) > 0) {
            $query->andWhere('m.type IN (:types)');
            $parameters['types'] = $types;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->execute();
    }
}
