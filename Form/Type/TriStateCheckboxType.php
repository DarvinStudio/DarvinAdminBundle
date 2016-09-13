<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tri-state checkbox form type
 */
class TriStateCheckboxType extends AbstractType
{
    const TRI_STATE_CHECKBOX_TYPE_CLASS = __CLASS__;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'boolean.1' => 1,
                'boolean.0' => 0,
            ],
            'choices_as_values' => true,
            'expanded'          => true,
            'empty_value'       => 'boolean.indeterminate',
            'attr'              => [
                'class' => 'tri_state_checkbox',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }
}
