<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use A2lix\AutoFormBundle\Form\Type\AutoFormType;
use A2lix\AutoFormBundle\ObjectInfo\DoctrineORMInfo;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Form object info
 */
class ObjectInfo extends DoctrineORMInfo
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * {@inheritDoc}
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        parent::__construct($classMetadataFactory);

        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldsConfig(string $class): array
    {
        $fieldsConfig = [];

        $metadata = $this->classMetadataFactory->getMetadataFor($class);

        if (!empty($fields = $metadata->getFieldNames())) {
            $fieldsConfig = array_fill_keys($fields, []);
        }
        if (!empty($assocNames = $metadata->getAssociationNames())) {
            $fieldsConfig += $this->getAssocsConfig($metadata, $assocNames);
        }

        return $fieldsConfig;
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata   Metadata
     * @param string[]                                           $assocNames Association names
     *
     * @return array
     */
    private function getAssocsConfig(ClassMetadata $metadata, array $assocNames): array
    {
        $assocsConfigs = [];

        foreach ($assocNames as $assocName) {
            if (!$metadata->isAssociationInverseSide($assocName)
                && (!$metadata instanceof ClassMetadataInfo || !$metadata->getAssociationMapping($assocName)['isCascadePersist'])
            ) {
                continue;
            }

            $class = $metadata->getAssociationTargetClass($assocName);

            if ($metadata->isSingleValuedAssociation($assocName)) {
                $nullable = ($metadata instanceof ClassMetadataInfo) && isset($metadata->discriminatorColumn['nullable']) && $metadata->discriminatorColumn['nullable'];

                $assocsConfigs[$assocName] = [
                    'field_type' => AutoFormType::class,
                    'data_class' => $class,
                    'required'   => !$nullable,
                ];

                continue;
            }

            $assocsConfigs[$assocName] = [
                'field_type'    => CollectionType::class,
                'entry_type'    => AutoFormType::class,
                'allow_add'     => true,
                'by_reference'  => false,
                'entry_options' => [
                    'data_class' => $class,
                ],
            ];
        }

        return $assocsConfigs;
    }
}
