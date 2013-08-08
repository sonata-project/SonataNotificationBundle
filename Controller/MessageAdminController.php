<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\NotificationBundle\Model\MessageInterface;

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
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
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
     * @return \Sonata\NotificationBundle\Model\MessageManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->get('sonata.notification.manager.message');
    }

    /**
     * @return \Sonata\NotificationBundle\Backend\BackendInterface
     */
    protected function getBackend()
    {
        return $this->get('sonata.notification.backend');
    }
}
