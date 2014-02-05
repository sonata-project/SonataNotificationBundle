<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\NotificationBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

/**
 * Class MessageController
 *
 * @package Sonata\NotificationBundle\Controller\Api
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
     * Constructor
     *
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(MessageManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Retrieves the list of messages (paginated)
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\NotificationBundle\Model\Message", "groups"="sonata_api_read"}
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
     * @return Message[]
     */
    public function getMessagesAction(ParamFetcherInterface $paramFetcher)
    {
        $page    = $paramFetcher->get('page');
        $count   = $paramFetcher->get('count');
        $orderBy = $paramFetcher->get('orderBy');

        $criteria = $paramFetcher->all();

        unset($criteria['page'], $criteria['count'], $criteria['orderBy']);

        foreach ($criteria as $key => $crit) {
            if (null === $crit) {
                unset($criteria[$key]);
            }
        }

        return $this->getMessageManager()->findBy($criteria, $orderBy, $count, $page);
    }

    /**
     * @return MessageManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->messageManager;
    }

}