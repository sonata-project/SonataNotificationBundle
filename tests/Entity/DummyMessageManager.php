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

namespace Sonata\NotificationBundle\Tests\Entity;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

final class DummyMessageManager implements MessageManagerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @phpstan-var class-string
     */
    private $class;

    /**
     * @phpstan-param class-string $class
     */
    public function __construct(string $class, ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->class = $class;
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $result = [];
        while (null !== $limit && $limit > 0) {
            $result[$limit] = new Message();
            --$limit;
        }

        return $result;
    }

    public function findByTypes(array $types, $state, $batchSize): array
    {
        $result = [];
        while (null !== $batchSize && $batchSize > 0) {
            $result[$batchSize] = new Message();
            --$batchSize;
        }

        return $result;
    }

    public function save($entity, $andFlush = true): void
    {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function findAll(): array
    {
        return [];
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return null;
    }

    public function find($id): ?object
    {
        return null;
    }

    public function create(): object
    {
        return new $this->class();
    }

    public function delete($entity, $andFlush = true): void
    {
    }

    public function getTableName(): string
    {
        throw new \LogicException('Not implemented.');
    }

    public function getConnection()
    {
        return $this->registry->getConnection();
    }

    public function countStates(): int
    {
        return 0;
    }

    public function cleanup($maxAge): void
    {
    }

    public function cancel(MessageInterface $message): void
    {
    }

    public function restart(MessageInterface $message): object
    {
        return $message;
    }

    public function findByAttempts(array $types, $state, $batchSize, $maxAttempts = null, $attemptDelay = 10): array
    {
        return [];
    }

    public function getPager(array $criteria, int $page, int $limit = 10, array $sort = []): PagerInterface
    {
        return Pager::create($this->createStub(QueryBuilder::class), $limit, $page);
    }
}
