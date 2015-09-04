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
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Metadata factory
 */
class MetadataFactory
{
    const CONTROLLER_ID_SUFFIX = '.admin.controller';

    const FORM_TYPE_NAME_PREFIX = 'admin_';

    const ROUTE_NAME_PREFIX = 'admin_';

    /**
     * @var \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var bool
     */
    private $isReflectionRecursive;

    /**
     * @var string
     */
    private $translatableTrait;

    /**
     * @param \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer                $classAnalyzer         Class analyzer
     * @param \Darvin\AdminBundle\Metadata\Configuration\ConfigurationLoader $configurationLoader   Configuration loader
     * @param \Doctrine\ORM\EntityManager                                    $em                    Entity manager
     * @param bool                                                           $isReflectionRecursive Is reflection recursive
     * @param string                                                         $translatableTrait     Translatable trait
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer,
        ConfigurationLoader $configurationLoader,
        EntityManager $em,
        $isReflectionRecursive,
        $translatableTrait
    ) {
        $this->classAnalyzer = $classAnalyzer;
        $this->configurationLoader = $configurationLoader;
        $this->em = $em;
        $this->isReflectionRecursive = $isReflectionRecursive;
        $this->translatableTrait = $translatableTrait;
    }

    /**
     * @param string $entityClass    Entity class
     * @param string $configPathname Configuration file pathname
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function create($entityClass, $configPathname)
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

        return new Metadata(
            $baseTranslationPrefix,
            $this->generateEntityTranslationPrefix($baseTranslationPrefix),
            $configuration,
            $this->generateControllerId($entityNamespace, $entityName),
            $entityClass,
            $entityName,
            $this->generateFormTypeName($entityName),
            $identifiers[0],
            $this->getMappings($doctrineMeta),
            $this->generateRoutingPrefix($entityName)
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
        $id = strtr($entityNamespace, array(
            'Bundle' => '',
            'Entity' => '',
            '\\'     => '',
        )).'.'.$entityName.self::CONTROLLER_ID_SUFFIX;

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

        if (!$this->classAnalyzer->hasTrait($doctrineMeta->getReflectionClass(), $this->translatableTrait, $this->isReflectionRecursive)) {
            return $mappings;
        }

        $translationClass = call_user_func(array($doctrineMeta->getName(), 'getTranslationEntityClass'));

        try {
            $translationDoctrineMeta = $this->em->getClassMetadata($translationClass);
        } catch (MappingException $ex) {
            throw $this->createUnableToGetDoctrineMetadataException($translationClass);
        }

        return array_merge($mappings, $translationDoctrineMeta->associationMappings, $translationDoctrineMeta->fieldMappings);
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
