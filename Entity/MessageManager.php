<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Entity;

use Doctrine\ORM\EntityManager;
use Sonata\CoreBundle\Entity\DoctrineBaseManager;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

class MessageManager extends DoctrineBaseManager implements MessageManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function save($message, $andFlush = true)
    {
        //Hack for ConsumerHandlerCommand->optimize()
        if ($message->getId() && !$this->om->getUnitOfWork()->isInIdentityMap($message)) {
            $this->om->getUnitOfWork()->merge($message);
        }

        parent::save($message, $andFlush);
    }

    /**
     * Find messages by types and states
     *
     * @param array $types
     * @param int   $state
     * @param int   $batchSize
     *
     * @return mixed
     */
    public function findByTypes(array $types, $state, $batchSize)
    {
        $params = array();
        $query = $this->prepareStateQuery($state, $types, $batchSize, $params);

        $query->setParameters($params);

        return $query->getQuery()->execute();
    }

    /**
     * Find messages by types, states and attempts
     *
     * @param array $types
     * @param int   $state
     * @param int   $batchSize
     * @param int   $maxAttempts
     * @param int   $attemptDelay
     *
     * @return mixed
     */
    public function findByAttempts(array $types, $state, $batchSize, $maxAttempts = null, $attemptDelay = 10)
    {
        $params = array();
        $query = $this->prepareStateQuery($state, $types, $batchSize, $params);

        if ($maxAttempts) {
            $query
                ->andWhere('m.restartCount < :maxAttempts')
                ->andWhere('m.updatedAt < :delayDate');

            $params['maxAttempts'] = $maxAttempts;
            $now = new \DateTime();
            $params['delayDate'] = $now->add(\DateInterval::createFromDateString(($attemptDelay * -1) . ' second'));
        }

        $query->setParameters($params);

        return $query->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function countStates()
    {
        $tableName = $this->om->getClassMetadata($this->class)->table['name'];

        $stm = $this->om->getConnection()->query(sprintf('SELECT state, count(state) as cnt FROM %s GROUP BY state', $tableName));

        $states = array(
            MessageInterface::STATE_DONE        => 0,
            MessageInterface::STATE_ERROR       => 0,
            MessageInterface::STATE_IN_PROGRESS => 0,
            MessageInterface::STATE_OPEN        => 0,
        );

        foreach ($stm->fetch() as $data) {
            $states[$data['state']] = $data['cnt'];
        }

        return $states;
    }

    /**
     * {@inheritDoc}
     */
    public function cleanup($maxAge)
    {
        $tableName = $this->om->getClassMetadata($this->class)->table['name'];

        $date = new \DateTime('now');
        $date->sub(new \DateInterval(sprintf('PT%sS', $maxAge)));

        $qb = $this->getRepository()->createQueryBuilder('message')
            ->delete()
            ->where('message.state = :state')
            ->andWhere('message.completedAt < :date')
            ->setParameter('state', MessageInterface::STATE_DONE)
            ->setParameter('date', $date);

        $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(MessageInterface $message)
    {
        if ($message->isRunning() || $message->isError()) {
            return;
        }

        $message->setState(MessageInterface::STATE_CANCELLED);

        $this->save($message);
    }

    /**
     * {@inheritdoc}
     */
    public function restart(MessageInterface $message)
    {
        if ($message->isOpen() || $message->isRunning()) {
            return;
        }

        $this->cancel($message);

        $count = $message->getRestartCount();

        $newMessage = clone $message;
        $newMessage->setRestartCount($count + 1);

        return $newMessage;
    }

    /**
     * @param int   $state
     * @param array $types
     * @param int   $batchSize
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function prepareStateQuery($state, $types, $batchSize, &$parameters)
    {
        $query = $this->getRepository()
            ->createQueryBuilder('m')
            ->where('m.state = :state')
            ->orderBy('m.createdAt');

        $parameters['state'] = $state;

        if (count($types) > 0) {
            if (array_key_exists('exclude', $types) || array_key_exists('include', $types)) {
                if (array_key_exists('exclude', $types)) {
                    $query->andWhere('m.type NOT IN (:exclude)');
                    $parameters['exclude'] = $types['exclude'];
                }

                if (array_key_exists('include', $types)) {
                    $query->andWhere('m.type IN (:include)');
                    $parameters['include'] = $types['include'];
                }
            } else { // BC
                $query->andWhere('m.type IN (:types)');
                $parameters['types'] = $types;
            }
        }

        $query->setMaxResults($batchSize);

        return $query;
    }
}
