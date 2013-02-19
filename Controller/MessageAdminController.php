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
     * @param \Sonata\NotificationBundle\Model\MessageInterface $message
     */
    protected function cancelMessage(MessageInterface $message)
    {
        if ($message->isRunning() || $message->isError()) {
            return;
        }

        $message->setState(MessageInterface::STATE_CANCELLED);

        $this->admin->getModelManager()->update($message);
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionPublish(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $message) {
            if ($message->isOpen()) {
                continue;
            }

            $this->cancelMessage($message);

            $count = $message->getRestartCount();

            $message = clone $message;
            $message->setRestartCount($count + 1);

            $this->get('sonata.notification.backend')->publish($message);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionCancelled(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $message) {
            $this->cancelMessage($message);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }
}
