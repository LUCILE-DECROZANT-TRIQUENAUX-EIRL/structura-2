<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud, Filters, KeyValueStore};
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\{ArrayField, DateTimeField, IdField, TextField};
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Symfony\Component\Form\Extension\Core\Type\{PasswordType, RepeatedType};
use Symfony\Component\Form\{FormBuilderInterface, FormEvents};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        // disable the softdeletable filter to show deleted users
        $this->entityManager->getFilters()->disable('softdeleteable');

        // remove actions if the user is deleted
        $editFromIndexAction = parent::configureActions($actions)
            ->getAsDto(Crud::PAGE_INDEX)
            ->getAction(Crud::PAGE_INDEX, Action::EDIT);
        if (!is_null($editFromIndexAction)) {
            $editFromIndexAction->setDisplayCallable(function (User $user) {
                return empty($user->getDeletedAt());
            });
        }
        $deleteFromIndexAction = parent::configureActions($actions)
            ->getAsDto(Crud::PAGE_INDEX)
            ->getAction(Crud::PAGE_INDEX, Action::DELETE);
        if (!is_null($deleteFromIndexAction)) {
            $deleteFromIndexAction->setDisplayCallable(function (User $user) {
                return empty($user->getDeletedAt());
            });
        }
        $editFromDetailAction = parent::configureActions($actions)
            ->getAsDto(Crud::PAGE_DETAIL)
            ->getAction(Crud::PAGE_DETAIL, Action::EDIT);
        if (!is_null($editFromDetailAction)) {
            $editFromDetailAction->setDisplayCallable(function (User $user) {
                return empty($user->getDeletedAt());
            });
        }
        $deleteFromDetailAction = parent::configureActions($actions)
            ->getAsDto(Crud::PAGE_DETAIL)
            ->getAction(Crud::PAGE_DETAIL, Action::DELETE);
        if (!is_null($deleteFromDetailAction)) {
            $deleteFromDetailAction->setDisplayCallable(function (User $user) {
                return empty($user->getDeletedAt());
            });
        }
        $deleteFromEditAction = Action::new('delete', 'Delete')
            ->displayIf(static function (User $user) {
                return empty($user->getDeletedAt());
            })
            ->linkToCrudAction(Action::DELETE)
            ->addCssClass('text-danger')
            ->setIcon('fa fa-trash-o')
        ;

        // add actions to menus
        return $actions
            // user listing
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // user edition page
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $deleteFromEditAction)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('username'),
            ArrayField::new('roles'),
            DateTimeField::new('deleted_at')->hideOnForm(),
        ];

        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'hash_property_path' => 'password',
                ],
                'second_options' => ['label' => '(Repeat)'],
                'mapped' => false,
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
        ;
        $fields[] = $password;

        return $fields;
    }

    // Configure filters on user index view
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('username')
            ->add(NullFilter::new('deletedAt', 'Statut de suppression')
                ->setChoiceLabels('Actifves', 'Supprimé-es'))
        ;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword() {
        return function($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($form->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}
