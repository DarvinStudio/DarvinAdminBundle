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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Batch delete form type
 */
class BatchDeleteType extends AbstractType
{
    const BATCH_DELETE_TYPE_CLASS = __CLASS__;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('entities', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'label'        => false,
            'class'        => $options['entity_class'],
            'choices'      => isset($options['entities']) ? $options['entities'] : null,
            'multiple'     => true,
            'expanded'     => true,
            'choice_label' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_token_id' => md5(__FILE__.__METHOD__.$this->getBlockPrefix()),
            ])
            ->setRequired('entity_class')
            ->setDefined('entities')
            ->setAllowedTypes([
                'entities'     => 'array',
                'entity_class' => 'string',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_batch_delete';
    }
}
