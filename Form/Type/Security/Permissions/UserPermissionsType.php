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

use Darvin\AdminBundle\Security\Permissions\UserPermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * User permissions form type
 */
class UserPermissionsType extends AbstractType
{
    const USER_PERMISSIONS_TYPE_CLASS = __CLASS__;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', array(
                'label' => false,
            ))
            ->add('permissions', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', array(
                'label'         => false,
                'entry_type'    => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                'entry_options' => array(
                    'required' => false,
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $permissionsField = $view->children['permissions'];

        /** @var \Symfony\Component\Form\FormView $child */
        foreach ($permissionsField->children as $name => $child) {
            $child->vars['label'] = 'security.permission.'.$name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
            'data_class'    => UserPermissions::USER_PERMISSIONS_CLASS,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_security_user_permissions';
    }
}
