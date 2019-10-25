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

/**
 * Metadata
 */
class Metadata
{
    /**
     * @var \Darvin\AdminBundle\Metadata\AssociatedMetadata|null
     */
    private $parent;

    /**
     * @var \Darvin\AdminBundle\Metadata\AssociatedMetadata[]
     */
    private $children;

    /**
     * @var string
     */
    private $baseTranslationPrefix;

    /**
     * @var string
     */
    private $entityTranslationPrefix;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $controllerId;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var bool
     */
    private $entityAbstract;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $filterFormTypeName;

    /**
     * @var string
     */
    private $formTypeName;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $mappings;

    /**
     * @var string
     */
    private $routingPrefix;

    /**
     * @var string|null
     */
    private $translationClass;

    /**
     * @param string      $baseTranslationPrefix   Base translation prefix
     * @param string      $entityTranslationPrefix Entity translation prefix
     * @param array       $configuration           Configuration
     * @param string      $controllerId            Controller service ID
     * @param string      $entityClass             Entity class
     * @param bool        $entityAbstract          Is entity class abstract
     * @param string      $entityName              Entity name
     * @param string      $filterFormTypeName      Filter form type name
     * @param string      $formTypeName            Form type name
     * @param string      $identifier              Identifier
     * @param array       $mappings                Mappings
     * @param string      $routingPrefix           Routing prefix
     * @param string|null $translationClass        Translation class
     */
    public function __construct(
        string $baseTranslationPrefix,
        string $entityTranslationPrefix,
        array $configuration,
        string $controllerId,
        string $entityClass,
        bool $entityAbstract,
        string $entityName,
        string $filterFormTypeName,
        string $formTypeName,
        string $identifier,
        array $mappings,
        string $routingPrefix,
        ?string $translationClass
    ) {
        $this->baseTranslationPrefix = $baseTranslationPrefix;
        $this->entityTranslationPrefix = $entityTranslationPrefix;
        $this->configuration = $configuration;
        $this->controllerId = $controllerId;
        $this->entityClass = $entityClass;
        $this->entityAbstract = $entityAbstract;
        $this->entityName = $entityName;
        $this->filterFormTypeName = $filterFormTypeName;
        $this->formTypeName = $formTypeName;
        $this->identifier = $identifier;
        $this->mappings = $mappings;
        $this->routingPrefix = $routingPrefix;
        $this->translationClass = $translationClass;

        $this->parent   = null;
        $this->children = [];
    }

    /**
     * @param string $property Property name
     *
     * @return bool
     */
    public function isAssociation(string $property): bool
    {
        return isset($this->mappings[$property]['targetEntity']);
    }

    /**
     * @return bool
     */
    public function isFilterFormEnabled(): bool
    {
        return !empty($this->configuration['form']['filter']['type'])
            || !empty($this->configuration['form']['filter']['fields']);
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\AssociatedMetadata|null
     */
    public function getParent(): ?AssociatedMetadata
    {
        return $this->parent;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\AssociatedMetadata|null $parent parent
     *
     * @return Metadata
     */
    public function setParent(?AssociatedMetadata $parent): Metadata
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\AssociatedMetadata $child Child
     *
     * @return Metadata
     */
    public function addChild(AssociatedMetadata $child): Metadata
    {
        $this->children[$child->getMetadata()->getEntityClass()] = $child;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\AssociatedMetadata[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param string $entityClass Child entity class
     *
     * @return bool
     */
    public function hasChild(string $entityClass): bool
    {
        return isset($this->children[$entityClass]);
    }

    /**
     * @param string $entityClass Child entity class
     *
     * @return \Darvin\AdminBundle\Metadata\AssociatedMetadata
     * @throws \InvalidArgumentException
     */
    public function getChild(string $entityClass): AssociatedMetadata
    {
        if (!isset($this->children[$entityClass])) {
            throw new \InvalidArgumentException(sprintf('Child "%s" does not exist.', $entityClass));
        }

        return $this->children[$entityClass];
    }

    /**
     * @return string
     */
    public function getBaseTranslationPrefix(): string
    {
        return $this->baseTranslationPrefix;
    }

    /**
     * @return string
     */
    public function getEntityTranslationPrefix(): string
    {
        return $this->entityTranslationPrefix;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration configuration
     *
     * @return Metadata
     */
    public function setConfiguration(array $configuration): Metadata
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return string
     */
    public function getControllerId(): string
    {
        return $this->controllerId;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return bool
     */
    public function isEntityAbstract(): bool
    {
        return $this->entityAbstract;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getFilterFormTypeName(): string
    {
        return $this->filterFormTypeName;
    }

    /**
     * @return string
     */
    public function getFormTypeName(): string
    {
        return $this->formTypeName;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @return string
     */
    public function getRoutingPrefix(): string
    {
        return $this->routingPrefix;
    }

    /**
     * @return string|null
     */
    public function getTranslationClass(): ?string
    {
        return $this->translationClass;
    }
}
