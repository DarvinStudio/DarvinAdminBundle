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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * User permissions form type
 */
class UserPermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'label' => false,
            ])
            ->add('permissions', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'label'         => false,
                'entry_type'    => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                'entry_options' => [
                    'label_format' => 'security.permission.%name%',
                    'required'     => false,
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
            'data_class'    => UserPermissions::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_security_user_permissions';
    }
}
