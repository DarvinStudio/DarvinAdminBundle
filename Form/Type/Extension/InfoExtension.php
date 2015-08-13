<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 13.08.15
 * Time: 11:24
 */

namespace Darvin\AdminBundle\Form\Type\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Info form type extension
 */
class InfoExtension extends AbstractTypeExtension
{
    const OPTION_INFO = 'darvin_admin_info';

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars[self::OPTION_INFO] = $options[self::OPTION_INFO];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault(self::OPTION_INFO, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
