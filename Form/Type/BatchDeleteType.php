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

use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Batch delete form type
 */
class BatchDeleteType extends AbstractType
{
    const BATCH_DELETE_TYPE_CLASS = __CLASS__;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $idAccessor;

    /**
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor $idAccessor Identifier accessor
     */
    public function __construct(IdentifierAccessor $idAccessor)
    {
        $this->idAccessor = $idAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $idAccessor = $this->idAccessor;

        $resolver
            ->setDefaults([
                'choice_label'  => false,
                'multiple'      => true,
                'expanded'      => true,
                'csrf_token_id' => md5(__FILE__.__METHOD__.$this->getBlockPrefix()),
            ])
            ->setRequired('entities')
            ->setAllowedTypes('entities', 'array')
            ->setNormalizer('choices', function (Options $options) use ($idAccessor) {
                $ids = array_map(function ($entity) use ($idAccessor) {
                    return $idAccessor->getValue($entity);
                }, $options['entities']);

                return array_combine($ids, $ids);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_batch_delete';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }
}
