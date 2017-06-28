<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Document;

use Doctrine\MongoDB\ArrayIterator;
use Doctrine\ODM\MongoDB\Cursor;
use NotificationEngine\Sonata\NotificationBundle\Model\MessageInterface;
use NotificationEngine\Sonata\NotificationBundle\Model\MessageManagerInterface;
use Sonata\CoreBundle\Model\BaseDocumentManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

/**
 * @author Salma Khemiri <chakroun.salma@gmail.com>
 */
class MessageManager extends BaseDocumentManager implements MessageManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($message, $andFlush = true)
    {
        //Hack for ConsumerHandlerCommand->optimize()
        if ($message->getId() && !$this->dm->getUnitOfWork()->isInIdentityMap($message)) {
            $this->getDocumentManager()->getUnitOfWork()->merge($message);
        }

        parent::save($message, $andFlush);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTypes(array $types, $state, $batchSize)
    {
        $query = $this->prepareStateQuery($state, $types, $batchSize);

        $result = $query->getQuery()->execute();

        if ($result instanceof Cursor) {
            $result = $result->toArray();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findByAttempts(array $types, $state, $batchSize, $maxAttempts = null, $attemptDelay = 10)
    {
        $query = $this->prepareStateQuery($state, $types, $batchSize);

        if ($maxAttempts) {
            $now = new \DateTime();
            $delayDate = $now->add(\DateInterval::createFromDateString(($attemptDelay * -1).' second'));

            $query
                ->field('restartCount')->lt($maxAttempts)
                ->field('updatedAt')->lt($delayDate);
        }

        $result = $query->getQuery()->execute();

        if ($result instanceof Cursor) {
            $result = $result->toArray();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countStates()
    {
        $result = $this->getRepository()->createQueryBuilder('message')
                                        ->group(array('state' => 1), array('count' => 0))
                                        ->reduce('function (curr, result) { result.count++; }')
                                        ->getQuery()
                                        ->execute();

        $states = array(
            MessageInterface::STATE_DONE => 0,
            MessageInterface::STATE_ERROR => 0,
            MessageInterface::STATE_IN_PROGRESS => 0,
            MessageInterface::STATE_OPEN => 0,
        );

        if ($result instanceof ArrayIterator) {
            $result = $result->toArray();

            foreach ($result as $data) {
                $states[$data['state']] = $data['count'];
            }
        }

        return $states;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup($maxAge)
    {
        $date = new \DateTime('now');
        $date->sub(new \DateInterval(sprintf('PT%sS', $maxAge)));

        $qb = $this->getRepository()->createQueryBuilder('message')
                                    ->remove()
                                    ->field('state')->equals(MessageInterface::STATE_DONE)
                                    ->field('completedAt')->lt($date)
                                    ->getQuery()
                                    ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(MessageInterface $message, $force = false)
    {
        if (($message->isRunning() || $message->isError()) && !$force) {
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
        if ($message->isOpen() || $message->isRunning() || $message->isCancelled()) {
            return;
        }

        $this->cancel($message, true);

        $newMessage = clone $message;
        $newMessage->setRestartCount($message->getRestartCount() + 1);
        $newMessage->setType($message->getType());

        return $newMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->getRepository()
            ->createQueryBuilder('message');

        $fields = $this->getDocumentManager()->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!in_array($field, $fields)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->class));
            }
        }
        if (count($sort) == 0) {
            $sort = array('type' => 'ASC');
        }
        foreach ($sort as $field => $direction) {
            $query->sort(sprintf('m.%s', $field), strtoupper($direction));
        }

        $parameters = array();

        if (isset($criteria['type'])) {
            $query->field('type')->equals($criteria['type']);
        }

        if (isset($criteria['state'])) {
            $query->field('state')->equals($criteria['state']);
        }

        $pager = new Pager();

        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }

    /**
     * @param int   $state
     * @param array $types
     * @param int   $batchSize
     * @param array $parameters
     *
     * @return QueryBuilder
     */
    protected function prepareStateQuery($state, $types, $batchSize)
    {
        $query = $this->getRepository()
            ->createQueryBuilder('message')
            ->field('state')->equals($state)
            ->sort('createdAt');

        if (count($types) > 0) {
            if (array_key_exists('exclude', $types) || array_key_exists('include', $types)) {
                if (array_key_exists('exclude', $types)) {
                    $query->field('type')->notIn(array($types['exclude']));
                }

                if (array_key_exists('include', $types)) {
                    $query->field('type')->in(array($types['include']));
                }
            } else {
                $query->field('type')->in(array($types));
            }
        }

        $query->limit($batchSize);

        return $query;
    }
}
