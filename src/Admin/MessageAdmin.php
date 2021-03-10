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
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MessageAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Message';

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('edit')
            ->remove('create')
            ->remove('history')
        ;
    }

    protected function configureBatchActions($actions): array
    {
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

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('type')
            ->add('createdAt')
            ->add('startedAt')
            ->add('completedAt')
            ->add('getStateName')
            ->add('restartCount')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $class = $this->getClass();

        $filter
            ->add('type')
            ->add('state', null, [
                'field_type' => ChoiceType::class,
                'field_options' => ['choices' => $class::getStateList()],
            ])
        ;
    }
}
