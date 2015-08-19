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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Metadata manager
 */
class MetadataManager
{
    const CACHE_ID = 'darvinAdminMetadata';

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataPool
     */
    private $metadataPool;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private $metadata;

    /**
     * @param \Doctrine\Common\Cache\Cache              $cache        Cache
     * @param \Doctrine\ORM\EntityManager               $em           Entity manager
     * @param \Darvin\AdminBundle\Metadata\MetadataPool $metadataPool Metadata pool
     */
    public function __construct(Cache $cache, EntityManager $em, MetadataPool $metadataPool)
    {
        $this->cache = $cache;
        $this->em = $em;
        $this->metadataPool = $metadataPool;
        $this->initialized = false;
        $this->metadata = array();
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     */
    public function getAll()
    {
        $this->init();

        return $this->metadata;
    }

    /**
     * @param object $entity Entity
     *
     * @return array
     */
    public function getConfigurationByEntity($entity)
    {
        return $this->getByEntity($entity)->getConfiguration();
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     */
    public function getConfigurationByEntityClass($entityClass)
    {
        return $this->getByEntityClass($entityClass)->getConfiguration();
    }

    /**
     * @param object $entity Entity
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getByEntity($entity)
    {
        return $this->getByEntityClass(ClassUtils::getClass($entity));
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getByEntityClass($entityClass)
    {
        $this->init();

        if (!isset($this->metadata[$entityClass])) {
            $childClass = $entityClass;

            while ($parentClass = get_parent_class($childClass)) {
                if (isset($this->metadata[$parentClass])) {
                    return $this->metadata[$parentClass];
                }

                $childClass = $parentClass;
            }

            throw new MetadataException(sprintf('Unable to find metadata for entity "%s".', $entityClass));
        }

        return $this->metadata[$entityClass];
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }
        if (!$this->initFromCache()) {
            $this->initAndCache();
        }

        $this->buildTree(array_keys($this->metadata));

        $this->initialized = true;
    }

    private function initAndCache()
    {
        $this->metadata = $this->metadataPool->getAll();

        $serialized = serialize($this->metadata);

        if (!$this->cache->save(self::CACHE_ID, $serialized)) {
            throw new MetadataException('Unable to cache metadata.');
        }
    }

    /**
     * @return bool
     */
    private function initFromCache()
    {
        $cached = $this->cache->fetch(self::CACHE_ID);

        if (false === $cached) {
            return false;
        }

        $unserialized = @unserialize($cached);

        if (!is_array($unserialized)) {
            return false;
        }

        $this->metadata = $unserialized;

        return true;
    }

    /**
     * @param array $parentEntities Parent entity classes
     *
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function buildTree(array $parentEntities)
    {
        foreach ($parentEntities as $parentEntity) {
            $parentMeta = $this->metadata[$parentEntity];
            $parentConfiguration = $parentMeta->getConfiguration();

            foreach ($parentConfiguration['child_entities'] as $childEntity) {
                if (!isset($this->metadata[$childEntity])) {
                    throw new MetadataException(sprintf('Unable to find metadata for entity "%s".', $childEntity));
                }

                $childMeta = $this->metadata[$childEntity];
                $associated = false;

                foreach ($childMeta->getMappings() as $property => $mapping) {
                    if (!$childMeta->isAssociation($property) || $mapping['targetEntity'] !== $parentEntity) {
                        continue;
                    }

                    $childMeta->setParent(new AssociatedMetadata($property, $parentMeta));
                    $parentMeta->addChild(new AssociatedMetadata($property, $childMeta));
                    $associated = true;
                }
                if (!$associated) {
                    throw new MetadataException(
                        sprintf('Entity "%s" is not associated with entity "%s".', $childEntity, $parentEntity)
                    );
                }
            }

            $this->buildTree($parentConfiguration['child_entities']);
        }
    }
}
