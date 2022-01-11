<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class UserPanel extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, ['label' => 'Nom'])
            ->add('email')
            ->add('roles', null, ['label' => 'Rôles'])
            ->add('isActive', null, ['label' => 'Activé']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, ['label' => 'Nom'])
            ->add('email')
            ->add('roles', FieldDescriptionInterface::TYPE_ARRAY, [
                'label' => 'Rôles',
                'inline' => false,
                'display' => 'values'
            ])
            ->add('isActive', null, ['label' => 'Activé', 'editable' => true]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, ['label' => 'Nom'])
            ->add('email')
            ->add('roles', null, ['label' => 'Rôles'])
            ->add('isActive', null, ['label' => 'Activé']);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name', null, ['label' => 'Nom'])
            ->add('email')
            ->add('roles', null, ['label' => 'Rôles'])
            ->add('isActive', null, ['label' => 'Activé']);
    }

    public function toString(object $object): string
    {
        return $object instanceof User ? $object->getName() : 'utilisateur';
    }
}
