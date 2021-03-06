<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

/**
 * Metadata factory
 */
class MetadataFactory
{
    private const FILTER_FORM_TYPE_NAME_SUFFIX = '_filter';
    private const FORM_TYPE_NAME_PREFIX        = 'admin_';
    private const ROUTE_NAME_PREFIX            = 'admin_';

    /**
     * @var \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader
     */
    private $configLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader $configLoader Configuration loader
     * @param \Doctrine\ORM\EntityManager                                    $em           Entity manager
     */
    public function __construct(ConfigurationLoader $configLoader, EntityManager $em)
    {
        $this->configLoader = $configLoader;
        $this->em = $em;
    }

    /**
     * @param string $entityName     Entity name
     * @param string $entityClass    Entity class
     * @param string $configPathname Configuration file pathname
     * @param string $controllerId   Controller service ID
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function createMetadata(string $entityName, string $entityClass, string $configPathname, string $controllerId): Metadata
    {
        try {
            $doctrineMeta = $this->em->getClassMetadata($entityClass);
        } catch (MappingException $ex) {
            throw $this->createUnableToGetDoctrineMetadataException($entityClass);
        }

        $baseTranslationPrefix = $this->generateBaseTranslationPrefix($entityName);

        $formTypeName = $this->generateFormTypeName($entityName);

        $translationClass = null;

        if (is_a($entityClass, TranslatableInterface::class, true)) {
            /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $entityClass */
            $translationClass = $entityClass::getTranslationEntityClass();
        }

        return new Metadata(
            $baseTranslationPrefix,
            $this->generateEntityTranslationPrefix($baseTranslationPrefix),
            $this->configLoader->load($configPathname),
            $controllerId,
            $entityClass,
            $doctrineMeta->getReflectionClass()->isAbstract(),
            $entityName,
            $formTypeName.self::FILTER_FORM_TYPE_NAME_SUFFIX,
            $formTypeName,
            $doctrineMeta->getIdentifier()[0],
            $this->getMappings($doctrineMeta),
            $this->generateRoutingPrefix($entityName),
            $translationClass
        );
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateBaseTranslationPrefix(string $entityName): string
    {
        return sprintf('%s.', $entityName);
    }

    /**
     * @param string $baseTranslationPrefix Base translation prefix
     *
     * @return string
     */
    private function generateEntityTranslationPrefix(string $baseTranslationPrefix): string
    {
        return sprintf('%sentity.', $baseTranslationPrefix);
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateFormTypeName(string $entityName): string
    {
        return self::FORM_TYPE_NAME_PREFIX.$entityName;
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateRoutingPrefix(string $entityName): string
    {
        return self::ROUTE_NAME_PREFIX.$entityName;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $doctrineMeta Doctrine metadata
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function getMappings(ClassMetadataInfo $doctrineMeta): array
    {
        $mappings = array_merge($doctrineMeta->associationMappings, $doctrineMeta->fieldMappings);

        if (!is_a($doctrineMeta->getName(), TranslatableInterface::class, true)) {
            return $mappings;
        }

        /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $translatableClass */
        $translatableClass = $doctrineMeta->getName();

        $translationClass = $translatableClass::getTranslationEntityClass();

        try {
            $translationDoctrineMeta = $this->em->getClassMetadata($translationClass);
        } catch (MappingException $ex) {
            throw $this->createUnableToGetDoctrineMetadataException($translationClass);
        }

        $translationMappings = array_merge($translationDoctrineMeta->associationMappings, $translationDoctrineMeta->fieldMappings);

        foreach ($translationMappings as &$attr) {
            $attr['translation'] = true;
        }

        unset($attr);

        return array_merge($mappings, $translationMappings);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function createUnableToGetDoctrineMetadataException(string $entityClass): MetadataException
    {
        return new MetadataException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
    }
}
