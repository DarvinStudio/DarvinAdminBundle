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

use Darvin\AdminBundle\Security\Permissions\AdministratorPermissions;
use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
use Darvin\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\UserBundle\Entity\User[]
     */
    private $users;

    /**
     * @var bool
     */
    private $usersLoaded;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->users = array();
        $this->usersLoaded = false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userRepository = $this->getUserRepository();

        $builder
            ->add('objectClass', 'hidden', array(
                'label' => false,
            ))
            ->add('administratorPermissionsSet', 'collection', array(
                'label' => false,
                'type'  => new AdministratorPermissionsType(),
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($userRepository) {
                /** @var \Darvin\AdminBundle\Security\Permissions\ObjectPermissions $objectPermissions */
                $objectPermissions = $event->getData();

                $users = $this->getUsers();

                foreach ($users as $id => $user) {
                    if ($objectPermissions->hasAdministratorPermissions($id)) {
                        continue;
                    }

                    $objectPermissions->addAdministratorPermissions(
                        $id,
                        new AdministratorPermissions($user->getId(), $user->getDefaultPermissions())
                    );
                }
                foreach ($objectPermissions->getAdministratorPermissionsSet() as $userId => $permissions) {
                    if (!isset($users[$userId])) {
                        $objectPermissions->removeAdministratorPermissions($userId);
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
        foreach ($view->children['administratorPermissionsSet'] as $userId => $child) {
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
     * @return \Darvin\UserBundle\Entity\User[]
     */
    private function getUsers()
    {
        if (!$this->usersLoaded) {
            /** @var \Darvin\UserBundle\Entity\User $user */
            foreach ($this->getUserRepository()->getNotSuperadminsBuilder()->getQuery()->getResult() as $user) {
                $this->users[$user->getId()] = $user;
            }
        }

        $this->usersLoaded = true;

        return $this->users;
    }

    /**
     * @return \Darvin\UserBundle\Repository\UserRepository
     */
    private function getUserRepository()
    {
        return $this->em->getRepository(User::USER_CLASS);
    }
}
