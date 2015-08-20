<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Login form type
 */
class LoginType extends AbstractType
{
    /**
     * @var string
     */
    private $intention;

    /**
     * @param string $intention Intention
     */
    public function __construct($intention)
    {
        $this->intention = $intention;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', null, array(
                'label' => 'security.model.username',
            ))
            ->add('_password', 'password', array(
                'label' => 'security.model.password',
            ))
            ->add('_remember_me', 'checkbox', array(
                'label'    => 'security.model.remember_me',
                'required' => false,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_field_name'    => '_csrf_token',
            'intention'          => $this->intention,
            'translation_domain' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return null;
    }
}
