<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class MessageAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('type')
            ->add('body')
            ->add('state')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('type')
            ->add('state')
            ->add('createdAt')
            ->add('startedAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type')
            ->add('state')
            ->add('createdAt')
            ->add('startedAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('type')
            ->add('body', 'textarea')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('restart', $this->getRouterIdParameter().'/restart');
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['restart'] = array(
            'label'            => $this->trans('restart'),
            'ask_confirmation' => true
        );

        return $actions;
    }
}