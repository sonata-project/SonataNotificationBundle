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
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

class MessageManager implements MessageManagerInterface
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
     * {@inheritDoc}
     */
    public function save(MessageInterface $message)
    {
        $this->em->persist($message);
        $this->em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(MessageInterface $message)
    {
        $this->em->remove($message);
        $this->em->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        return new $this->class;
    }

    /**
     * Returns the next open message available in the stack
     *
     * @return MessageInterface
     */
    public function getNextOpenMessage($pause = 250000)
    {
        $tableName = $this->em->getClassMetadata($this->class)->table['name'];

        $locked = false;
        try {
            while(true) {
                $this->em->getConnection()->exec(sprintf('LOCK TABLES %s as t0 WRITE', $tableName));
                $locked = true;

                $message = $this->findOneBy(array('state' => MessageInterface::STATE_OPEN));

                if (!$message) {
                    $this->em->getConnection()->exec(sprintf('UNLOCK TABLES'));
                    $locked = false;

                    usleep($pause);

                    continue;
                }

                $message->setState(MessageInterface::STATE_IN_PROGRESS);
                $this->save($message);

                $this->em->getConnection()->exec(sprintf('UNLOCK TABLES'));

                return $message;
            }
        }  catch(\Exception $e) {
            if ($locked) {
                $this->em->getConnection()->exec(sprintf('UNLOCK TABLES'));
            }

            throw $e;
        }
    }
}