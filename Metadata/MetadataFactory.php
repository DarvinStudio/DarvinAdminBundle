<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader;
use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Metadata factory
 */
class MetadataFactory
{
    const FILTER_FORM_TYPE_NAME_SUFFIX = '_filter';

    const FORM_TYPE_NAME_PREFIX = 'admin_';

    const ROUTE_NAME_PREFIX = 'admin_';

    /**
     * @var \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader
     */
    private $configLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @param \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader  $configLoader        Configuration loader
     * @param \Doctrine\ORM\EntityManager                                     $em                  Entity manager
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     */
    public function __construct(ConfigurationLoader $configLoader, EntityManager $em, TranslatableManagerInterface $translatableManager)
    {
        $this->configLoader = $configLoader;
        $this->em = $em;
        $this->translatableManager = $translatableManager;
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
    public function createMetadata($entityName, $entityClass, $configPathname, $controllerId): Metadata
    {
        try {
            $doctrineMeta = $this->em->getClassMetadata($entityClass);
        } catch (MappingException $ex) {
            throw $this->createUnableToGetDoctrineMetadataException($entityClass);
        }

        $baseTranslationPrefix = $this->generateBaseTranslationPrefix($entityName);

        $formTypeName = $this->generateFormTypeName($entityName);

        return new Metadata(
            $baseTranslationPrefix,
            $this->generateEntityTranslationPrefix($baseTranslationPrefix),
            $this->configLoader->load($configPathname),
            $controllerId,
            $entityClass,
            $entityName,
            $formTypeName.self::FILTER_FORM_TYPE_NAME_SUFFIX,
            $formTypeName,
            $doctrineMeta->getIdentifier()[0],
            $this->getMappings($doctrineMeta),
            $this->generateRoutingPrefix($entityName),
            $this->translatableManager->isTranslatable($entityClass) ? $this->translatableManager->getTranslationClass($entityClass) : null
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

        if (!$this->translatableManager->isTranslatable($doctrineMeta->getName())) {
            return $mappings;
        }

        $translationClass = $this->translatableManager->getTranslationClass($doctrineMeta->getName());

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
