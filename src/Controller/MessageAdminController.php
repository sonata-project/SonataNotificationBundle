<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageAdminController extends CRUDController
{
    /**
     * @param ProxyQueryInterface $query
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function batchActionPublish(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $message) {
            $message = $this->getMessageManager()->restart($message);

            $this->getBackend()->publish($message);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function batchActionCancelled(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $message) {
            $this->getMessageManager()->cancel($message);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @return MessageManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->get('sonata.notification.manager.message');
    }

    /**
     * @return BackendInterface
     */
    protected function getBackend()
    {
        return $this->get('sonata.notification.backend');
    }
}
