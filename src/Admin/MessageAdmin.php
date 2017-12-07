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

namespace Sonata\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;

class MessageAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection): void
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
        $actions = [];
        $actions['publish'] = [
            'label' => $this->getLabelTranslatorStrategy()->getLabel('publish', 'batch', 'message'),
            'translation_domain' => $this->getTranslationDomain(),
            'ask_confirmation' => false,
        ];

        $actions['cancelled'] = [
            'label' => $this->getLabelTranslatorStrategy()->getLabel('cancelled', 'batch', 'message'),
            'translation_domain' => $this->getTranslationDomain(),
            'ask_confirmation' => false,
        ];

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
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
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $class = $this->getClass();

        $datagridMapper
            ->add('type')
            ->add('state', null, [], ChoiceFilter::class, ['choices' => $class::getStateList()])
        ;
    }
}
