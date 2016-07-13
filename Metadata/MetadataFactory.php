<?php
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
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Metadata factory
 */
class MetadataFactory
{
    const CONTROLLER_ID_SUFFIX = '.admin.controller';

    const FILTER_FORM_TYPE_NAME_SUFFIX = '_filter';

    const FORM_TYPE_NAME_PREFIX = 'admin_';

    const ROUTE_NAME_PREFIX = 'admin_';

    /**
     * @var \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @param \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader  $configurationLoader Configuration loader
     * @param \Doctrine\ORM\EntityManager                                     $em                  Entity manager
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     */
    public function __construct(
        ConfigurationLoader $configurationLoader,
        EntityManager $em,
        TranslatableManagerInterface $translatableManager
    ) {
        $this->configurationLoader = $configurationLoader;
        $this->em = $em;
        $this->translatableManager = $translatableManager;
    }

    /**
     * @param string $entityClass    Entity class
     * @param string $configPathname Configuration file pathname
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function createMetadata($entityClass, $configPathname)
    {
        try {
            $doctrineMeta = $this->em->getClassMetadata($entityClass);
        } catch (MappingException $ex) {
            throw $this->createUnableToGetDoctrineMetadataException($entityClass);
        }

        $configuration = $this->configurationLoader->load($configPathname);

        $entityNamespace = $this->detectEntityNamespace(
            $doctrineMeta->getName(),
            $this->em->getConfiguration()->getEntityNamespaces()
        );

        $entityName = isset($configuration['entity_name'])
            ? $configuration['entity_name']
            : $this->generateEntityName($doctrineMeta->getName(), $entityNamespace);

        $baseTranslationPrefix = $this->generateBaseTranslationPrefix($entityName);

        $identifiers = $doctrineMeta->getIdentifier();

        $formTypeName = $this->generateFormTypeName($entityName);

        return new Metadata(
            $baseTranslationPrefix,
            $this->generateEntityTranslationPrefix($baseTranslationPrefix),
            $configuration,
            $this->generateControllerId($entityNamespace, $entityName),
            $entityClass,
            $entityName,
            $formTypeName.self::FILTER_FORM_TYPE_NAME_SUFFIX,
            $formTypeName,
            $identifiers[0],
            $this->getMappings($doctrineMeta),
            $this->generateRoutingPrefix($entityName),
            $this->translatableManager->isTranslatable($entityClass) ? $this->translatableManager->getTranslationClass($entityClass) : null
        );
    }

    /**
     * @param string $entityClass      Entity class
     * @param array  $entityNamespaces Entity namespaces
     *
     * @return string
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function detectEntityNamespace($entityClass, array $entityNamespaces)
    {
        foreach ($entityNamespaces as $namespace) {
            if (0 === strpos($entityClass, $namespace)) {
                return $namespace;
            }
        }

        throw new MetadataException(sprintf('Unable to detect namespace of entity "%s".', $entityClass));
    }

    /**
     * @param string $entityClass     Entity class
     * @param string $entityNamespace Entity namespace
     *
     * @return string
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function generateEntityName($entityClass, $entityNamespace)
    {
        $name = str_replace($entityNamespace.'\\', '', $entityClass);
        $parts = explode('\\', $name);
        $partsCount = count($parts);

        for ($i = 0; $i < $partsCount - 1; $i++) {
            if ($parts[$i] === $parts[$i + 1]) {
                unset($parts[$i]);
            }
        }

        return StringsUtil::toUnderscore(implode($parts));
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateBaseTranslationPrefix($entityName)
    {
        return $entityName.'.';
    }

    /**
     * @param string $baseTranslationPrefix Base translation prefix
     *
     * @return string
     */
    private function generateEntityTranslationPrefix($baseTranslationPrefix)
    {
        return $baseTranslationPrefix.'entity.';
    }

    /**
     * @param string $entityNamespace Entity namespace
     * @param string $entityName      Entity name
     *
     * @return string
     */
    private function generateControllerId($entityNamespace, $entityName)
    {
        $id = strtr($entityNamespace, [
            'Bundle' => '',
            'Entity' => '',
            '\\'     => '',
            ]
            ).'.'.$entityName.self::CONTROLLER_ID_SUFFIX;

        return StringsUtil::toUnderscore($id);
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateFormTypeName($entityName)
    {
        return self::FORM_TYPE_NAME_PREFIX.$entityName;
    }

    /**
     * @param string $entityName Entity name
     *
     * @return string
     */
    private function generateRoutingPrefix($entityName)
    {
        return self::ROUTE_NAME_PREFIX.$entityName;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $doctrineMeta Doctrine metadata
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function getMappings(ClassMetadataInfo $doctrineMeta)
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
    private function createUnableToGetDoctrineMetadataException($entityClass)
    {
        return new MetadataException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
    }
}
