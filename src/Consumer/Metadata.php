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

namespace Sonata\NotificationBundle\Consumer;

/**
 * @final since sonata-project/notification-bundle 3.13
 */
class Metadata
{
    /**
     * @var array
     */
    protected $informations;

    public function __construct(array $informations = [])
    {
        $this->informations = $informations;
    }

    /**
     * @return array
     */
    public function getInformations()
    {
        return $this->informations;
    }
}
