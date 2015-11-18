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
use Darvin\AdminBundle\Security\Permissions\UserPermissions;
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
    const OBJECT_PERMISSIONS_TYPE_CLASS = __CLASS__;

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
        $this->users = array();
        $this->usersLoaded = false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userRepository = $this->userRepository;

        $builder
            ->add('objectClass', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType', array(
                'label' => false,
            ))
            ->add('userPermissionsSet', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType', array(
                'label' => false,
                'type'  => new UserPermissionsType(),
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($userRepository) {
                /** @var \Darvin\AdminBundle\Security\Permissions\ObjectPermissions $objectPermissions */
                $objectPermissions = $event->getData();

                $users = $this->getUsers();

                foreach ($users as $id => $user) {
                    if ($objectPermissions->hasUserPermissions($id)) {
                        continue;
                    }

                    $objectPermissions->addUserPermissions(
                        $id,
                        new UserPermissions($user->getId(), $user->getDefaultPermissions())
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
        $view->vars['label'] = 'security.object.'.$view->vars['name'];

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
        $resolver->setDefaults(array(
            'data_class' => ObjectPermissions::OBJECT_PERMISSIONS_CLASS,
            'intention'  => md5(__FILE__.$this->getName()),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
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
            foreach ($this->userRepository->getNotSuperadminsBuilder()->getQuery()->getResult() as $user) {
                $this->users[$user->getId()] = $user;
            }
        }

        $this->usersLoaded = true;

        return $this->users;
    }
}
