<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MessageController.
 *
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageController
{
    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param MessageManagerInterface $messageManager
     * @param FormFactoryInterface    $formFactory
     */
    public function __construct(MessageManagerInterface $messageManager, FormFactoryInterface $formFactory)
    {
        $this->messageManager = $messageManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of messages (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for message list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of messages by page")
     * @QueryParam(name="orderBy", array=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     * @QueryParam(name="type", nullable=true, description="Message type filter")
     * @QueryParam(name="state", requirements="\d+", strict=true, nullable=true, description="Message status filter")
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getMessagesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = array(
            'state' => '',
            'type' => '',
        );

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        foreach ($criteria as $key => $value) {
            if (null === $value) {
                unset($criteria[$key]);
            }
        }

        if (!$sort) {
            $sort = array();
        } elseif (!is_array($sort)) {
            $sort = array($sort => 'asc');
        }

        return $this->getMessageManager()->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Adds a message.
     *
     * @ApiDoc(
     *  input={"class"="sonata_notification_api_form_message", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\NotificationBundle\Model\Message", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while message creation"
     *  }
     * )
     *
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request A Symfony request
     *
     * @return MessageInterface
     */
    public function postMessageAction(Request $request)
    {
        $message = null;

        $form = $this->formFactory->createNamed(null, 'sonata_notification_api_form_message', $message, array(
            'csrf_protection' => false,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $message = $form->getData();

            $this->messageManager->save($message);

            $view = FOSRestView::create($message);
            $serializationContext = SerializationContext::create();
            $serializationContext->setGroups(array('sonata_api_read'));
            $serializationContext->enableMaxDepthChecks();
            $view->setSerializationContext($serializationContext);

            return $view;
        }

        return $form;
    }

    /**
     * @return MessageManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->messageManager;
    }
}
