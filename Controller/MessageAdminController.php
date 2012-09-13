<?php

namespace Sonata\NotificationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageAdminController extends Controller
{
    /**
     * Reset statuses of selected Messages.
     *
     * @param mixed $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchActionRestart($query)
    {
        foreach ($query->execute() as $message) {
            $message->setRestartCount($message->getRestartCount() + 1);
            $message->setState(MessageInterface::STATE_OPEN);

            $this->getMessageManager()->save($message);
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @return \Sonata\NotificationBundle\Entity\MessageManager
     */
    protected function getMessageManager()
    {
        return $this->get('sonata.notification.manager.message');
    }
}