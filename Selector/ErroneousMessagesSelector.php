<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Selector;

use Doctrine\ORM\EntityManager;

use Sonata\NotificationBundle\Model\MessageInterface;

class ErroneousMessagesSelector
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em    = $em;
        $this->class = $class;
    }

    /**
     * Retrive messages with given type(s) and restrict to max attempts count.
     *
     * @param array $types
     * @param int $maxAttempts
     *
     * @return array
     */
    public function getMessages(array $types, $maxAttempts = 5)
    {
        $query = $this->em->getRepository($this->class)
            ->createQueryBuilder('m')
            ->where('m.state = :erroneousState')
            ->andWhere('m.restartCount < :maxAttempts');

        $parameters = array(
            'erroneousState' => MessageInterface::STATE_ERROR,
            'maxAttempts'    => $maxAttempts,
        );

        if (count($types) > 0) {
            $query->andWhere('m.type IN (:types)');
            $parameters['types']= $types;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->execute();
    }
}