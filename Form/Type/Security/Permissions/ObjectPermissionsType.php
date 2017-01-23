<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Security\Permissions;

use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\Security\Permissions\UserPermissions;
use Darvin\AdminBundle\Security\User\Roles;
use Darvin\UserBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Object permissions form type
 */
class ObjectPermissionsType extends AbstractType
{
    /**
     * @var \Darvin\UserBundle\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @var \Darvin\UserBundle\Entity\BaseUser[]
     */
    private $users;

    /**
     * @var bool
     */
    private $usersLoaded;

    /**
     * @param \Darvin\UserBundle\Repository\UserRepository $userRepository User entity repository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->users = [];
        $this->usersLoaded = false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $this->getUsers();

        $builder
            ->add('objectClass', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'label' => false,
            ])
            ->add('userPermissionsSet', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'label'      => false,
                'entry_type' => UserPermissionsType::class,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($users) {
                /** @var \Darvin\AdminBundle\Security\Permissions\ObjectPermissions $objectPermissions */
                $objectPermissions = $event->getData();

                foreach ($users as $id => $user) {
                    if ($objectPermissions->hasUserPermissions($id)) {
                        continue;
                    }

                    $objectPermissions->addUserPermissions(
                        $id,
                        new UserPermissions($user->getId(), Permission::getDefaultPermissions($user))
                    );
                }
                foreach ($objectPermissions->getUserPermissionsSet() as $userId => $permissions) {
                    if (!isset($users[$userId])) {
                        $objectPermissions->removeUserPermissions($userId);
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $users = $this->getUsers();

        /** @var \Symfony\Component\Form\FormView $child */
        foreach ($view->children['userPermissionsSet'] as $userId => $child) {
            $user = $users[$userId];
            $child->vars['label'] = $user->getEmail();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
            'data_class'    => ObjectPermissions::class,
            'label_format'  => 'securable.%name%',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_security_object_permissions';
    }

    /**
     * @return \Darvin\UserBundle\Entity\BaseUser[]
     */
    private function getUsers()
    {
        if (!$this->usersLoaded) {
            /** @var \Darvin\UserBundle\Entity\BaseUser $user */
            foreach ($this->userRepository->getByRolesBuilder(Roles::getRoles(), Roles::ROLE_SUPERADMIN)->getQuery()->getResult() as $user) {
                $this->users[$user->getId()] = $user;
            }
        }

        $this->usersLoaded = true;

        return $this->users;
    }
}
