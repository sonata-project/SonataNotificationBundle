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

namespace Sonata\NotificationBundle\Exception;

use Sonata\CoreBundle\Exception\InvalidParameterException as CoreBundleException;

if (class_exists(CoreBundleException::class)) {
    class InvalidParameterException extends CoreBundleException
    {
    }
} else {
    /**
     * @final since sonata-project/notification-bundle 3.x
     */
    class InvalidParameterException extends \RuntimeException
    {
    }
}
