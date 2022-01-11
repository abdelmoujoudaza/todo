<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Admin;
use Sonata\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminPanel extends AbstractAdmin
{
    private $userPasswordHasher;
    
    public function __construct(string $code, string $class, string $baseControllerName, UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->userPasswordHasher = $userPasswordHasher;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        if ($this->isGranted(Admin::ROLE_ADMIN) && ! $this->isGranted(Admin::ROLE_SUPER_ADMIN)) {
            $collection->clearExcept(['list', 'edit', 'create']);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email')
            ->add('roles');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('email')
            ->add('roles', FieldDescriptionInterface::TYPE_ARRAY, [
                'label'   => 'Rôles',
                'inline'  => false,
                'display' => 'values'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if ($this->isCurrentRoute('create')) {
            $form->add('email');
        }

        $form
            // ->add('roles', ChoiceType::class, [
            //     'multiple' => true,
            //     'choices' => [
            //         'Super Admin' => Admin::ROLE_SUPER_ADMIN,
            //         'Admin' => Admin::ROLE_ADMIN,
            //     ],
            // ])
            // ->add('roles', SecurityRolesType::class, ['multiple' => true])
            
            // ->add('roles', CollectionType::class, [
            //     'entry_type'    => ChoiceType::class,
            //     'entry_options' => [
            //             'choices' => [
            //                 'Super Admin' => Admin::ROLE_SUPER_ADMIN,
            //                 'Admin' => Admin::ROLE_ADMIN,
            //             ]
            //         ]
            //     ]
            // )
            ->add('password', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Password confirmation']
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('email')
            ->add('roles', FieldDescriptionInterface::TYPE_ARRAY, [
                'label'   => 'Rôles',
                'inline'  => false,
                'display' => 'values'
            ]);
    }

    public function prePersist(Object $object): void
    {
        parent::prePersist($object);
        $this->hashPassword($object);
    }
    
    public function preUpdate(Object $object): void
    {
        parent::preUpdate($object);
        $this->hashPassword($object);
    }

    private function hashPassword(Object $object): void
    {
        $password = $object->getpassword();
    
        $object->setPassword($this->userPasswordHasher->hashPassword(
            $object,
            $password
        ));
    } 

    public function toString(Object $object): string
    {
        return 'administrateur';
    }
}
