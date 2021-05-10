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

namespace Sonata\NotificationBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\NotificationBundle\Form\Type\MessageSerializationType;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hugo Briand <briand@ekino.com>
 *
 * @final since sonata-project/notification-bundle 3.x
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

    public function __construct(MessageManagerInterface $messageManager, FormFactoryInterface $formFactory)
    {
        $this->messageManager = $messageManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of messages (paginated).
     *
     * @Operation(
     *     tags={"/api/notification/messages"},
     *     summary="Retrieves the list of messages (paginated).",
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page for message list pagination",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="count",
     *         in="query",
     *         description="Number of messages per page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="query",
     *         description="Message type filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="state",
     *         in="query",
     *         description="Message status filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Query groups order by clause (key is field, value is direction)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\DatagridBundle\Pager\PagerInterface"))
     *     )
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Page for message list pagination")
     * @Rest\QueryParam(name="count", requirements="\d+", default="10", description="Number of messages per page")
     * @Rest\QueryParam(name="type", nullable=true, description="Message type filter")
     * @Rest\QueryParam(name="state", requirements="\d+", strict=true, nullable=true, description="Message status filter")
     * @Rest\QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Query groups order by clause (key is field, value is direction)")
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return PagerInterface
     */
    public function getMessagesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = [
            'state' => '',
            'type' => '',
        ];

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        $criteria = array_filter($criteria, static function ($value): bool {
            return null !== $value;
        });

        if (!$sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->getMessageManager()->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Adds a message.
     *
     * @Operation(
     *     tags={"/api/notification/messages"},
     *     summary="Adds a message.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\NotificationBundle\Model\Message"))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while message creation"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return FormInterface
     */
    public function postMessageAction(Request $request)
    {
        $message = null;

        $form = $this->formFactory->createNamed('', MessageSerializationType::class, $message, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $this->messageManager->save($message);

            return $message;
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
