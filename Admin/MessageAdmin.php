<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class MessageAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('edit')
            ->remove('create')
            ->remove('history')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = array();
        $actions['publish'] = array(
            'label' => $this->getLabelTranslatorStrategy()->getLabel('publish', 'batch', 'message'),
            'translation_domain' => $this->getTranslationDomain(),
            'ask_confirmation' => false,
        );

        $actions['cancelled'] = array(
            'label' => $this->getLabelTranslatorStrategy()->getLabel('cancelled', 'batch', 'message'),
            'translation_domain' => $this->getTranslationDomain(),
            'ask_confirmation' => false,
        );

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('type')
            ->add('createdAt')
            ->add('startedAt')
            ->add('completedAt')
            ->add('getStateName')
            ->add('body')
            ->add('restartCount')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('createdAt')
            ->add('startedAt')
            ->add('completedAt')
            ->add('getStateName')
            ->add('restartCount')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $class = $this->getClass();

        $datagridMapper
            ->add('type')
            ->add('state', null, array(), 'choice', array('choices' => $class::getStateList()))
        ;
    }
}
