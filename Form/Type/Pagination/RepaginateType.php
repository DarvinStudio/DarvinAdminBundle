<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Pagination;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Repaginate form type
 */
class RepaginateType extends AbstractType
{
    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Admin metadata manager
     */
    public function __construct(AdminMetadataManagerInterface $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config = $this->metadataManager->getConfiguration($options['entity_class'])['pagination'];

        $range = range($config['min'], $config['max'], $config['step']);

        $builder->add('itemsPerPage', ChoiceType::class, [
            'label'   => false,
            'choices' => array_combine($range, $range),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('entity_class')
            ->setAllowedTypes('entity_class', 'string');
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_repaginate';
    }
}
