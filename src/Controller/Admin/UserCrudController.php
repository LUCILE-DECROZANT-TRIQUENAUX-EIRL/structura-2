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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $displayIfIsNotDeletedFunction = function (Action $action) {
            $action->displayIf(static function (User $user) {
                return !$user->isDeleted();
            });
            return $action;
        };

        // define custom actions
        $restoreUser = Action::new('restore', 'Restore', 'fas fa-trash-restore-alt me-1 text-info')
            ->linkToCrudAction('restoreUser')
            ->addCssClass('text-info')
            ->displayIf(static function (User $user) {
                return $user->isDeleted();
            })
        ;
        $deleteUser = Action::new('delete', 'Delete')
            ->linkToCrudAction('delete')
            ->addCssClass('text-danger')
            ->displayIf(static function (User $user) {
                return !$user->isDeleted();
            })
        ;

        // add actions to menus
        return $actions
            // user listing
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $restoreUser)
            ->update(Crud::PAGE_INDEX, Action::EDIT, $displayIfIsNotDeletedFunction)
            ->update(Crud::PAGE_INDEX, Action::DELETE, $displayIfIsNotDeletedFunction)
            ->reorder(Crud::PAGE_INDEX, [
                Action::DETAIL,
                Action::EDIT,
                Action::DELETE,
                $restoreUser->getAsDto()->getName(),
            ])
            // user creation page
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->reorder(Crud::PAGE_NEW, [
                Action::INDEX,
                Action::SAVE_AND_ADD_ANOTHER,
                Action::SAVE_AND_RETURN,
            ])
            // user edition page
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, $deleteUser)
            ->reorder(Crud::PAGE_EDIT, [
                Action::INDEX,
                $deleteUser->getAsDto()->getName(),
                Action::SAVE_AND_CONTINUE,
                Action::SAVE_AND_RETURN,
            ])
            // user details page
            ->add(Crud::PAGE_DETAIL, $restoreUser)
            ->update(Crud::PAGE_DETAIL, Action::EDIT, $displayIfIsNotDeletedFunction)
            ->update(Crud::PAGE_DETAIL, Action::DELETE, $displayIfIsNotDeletedFunction)
            ->reorder(Crud::PAGE_DETAIL, [
                Action::INDEX,
                $restoreUser->getAsDto()->getName(),
                Action::EDIT,
                Action::DELETE,
            ])
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

    /**
     * Restore a deleted user
     *
     * @param AdminContext $context
     * @return RedirectResponse redirect to the entity index
     */
    public function restoreUser(AdminContext $context)
    {
        /** @var $user User */
        $user = $context->getEntity()->getInstance();
        $user->setDeletedAt(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirect('admin?crudAction=' . Crud::PAGE_INDEX . '&crudControllerFqcn=' . get_class($this));
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
