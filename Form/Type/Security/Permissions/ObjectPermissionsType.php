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

use Darvin\AdminBundle\Entity\Administrator;
use Darvin\AdminBundle\Security\Permissions\AdministratorPermissions;
use Darvin\AdminBundle\Security\Permissions\ObjectPermissions;
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
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $administratorRepository = $this->getAdministratorRepository();

        $builder
            ->add('objectClass', 'hidden', array(
                'label' => false,
            ))
            ->add('administratorPermissionsSet', 'collection', array(
                'label' => false,
                'type'  => new AdministratorPermissionsType(),
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($administratorRepository) {
                /** @var \Darvin\AdminBundle\Security\Permissions\ObjectPermissions $objectPermissions */
                $objectPermissions = $event->getData();

                $administrators = array();

                /** @var \Darvin\AdminBundle\Entity\Administrator $administrator */
                foreach ($administratorRepository->getNotSuperadminsBuilder()->getQuery()->getResult() as $administrator) {
                    $administrators[$administrator->getUsername()] = $administrator;
                }
                foreach ($administrators as $username => $administrator) {
                    if ($objectPermissions->hasAdministratorPermissions($username)) {
                        continue;
                    }

                    $objectPermissions->addAdministratorPermissions(
                        $username,
                        new AdministratorPermissions($administrator->getId(), $administrator->getDefaultPermissions())
                    );
                }
                foreach ($objectPermissions->getAdministratorPermissionsSet() as $username => $permissions) {
                    if (!isset($administrators[$username])) {
                        $objectPermissions->removeAdministratorPermissions($username);
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

        $administratorPermissionsSetField = $view->children['administratorPermissionsSet'];

        /** @var \Symfony\Component\Form\FormView $child */
        foreach ($administratorPermissionsSetField as $name => $child) {
            $child->vars['label'] = $name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ObjectPermissions::CLASS_NAME,
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
     * @return \Darvin\AdminBundle\Repository\AdministratorRepository
     */
    private function getAdministratorRepository()
    {
        return $this->em->getRepository(Administrator::CLASS_NAME);
    }
}
