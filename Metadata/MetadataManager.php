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

use Darvin\AdminBundle\Event\Metadata\MetadataEvent;
use Darvin\AdminBundle\Event\Metadata\MetadataEvents;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\Util\ClassUtils;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Metadata manager
 */
class MetadataManager implements AdminMetadataManagerInterface
{
    private const CACHE_KEY = 'metadata';

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    private $cache;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private $childMetadata;

    /**
     * @var array
     */
    private $hasMetadata;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]|null
     */
    private $metadata;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                   $entityResolver  Entity resolver
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Event dispatcher
     * @param \Darvin\AdminBundle\Metadata\MetadataPool                   $metadataPool    Metadata pool
     */
    public function __construct(EntityResolverInterface $entityResolver, EventDispatcherInterface $eventDispatcher, MetadataPool $metadataPool)
    {
        $this->entityResolver = $entityResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->metadataPool = $metadataPool;

        $this->childMetadata = $this->hasMetadata = [];
        $this->metadata = null;
    }

    /**
     * @param \Psr\SimpleCache\CacheInterface|null $cache Cache
     */
    public function setCache(?CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param object|string $entity Entity
     *
     * @return bool
     */
    public function hasMetadata($entity): bool
    {
        $class = $this->entityResolver->resolve(is_object($entity) ? ClassUtils::getClass($entity) : $entity);

        if (!isset($this->hasMetadata[$class])) {
            $metadata = null;

            try {
                $metadata = $this->getMetadata($class);
            } catch (MetadataException $ex) {
            }

            $this->hasMetadata[$class] = null !== $metadata;
        }

        return $this->hasMetadata[$class];
    }

    /**
     * @param object|string $entity Entity
     *
     * @return array
     */
    public function getConfiguration($entity): array
    {
        return $this->getMetadata($entity)->getConfiguration();
    }

    /**
     * @param object|string $entity Entity
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getMetadata($entity): Metadata
    {
        $class    = $this->entityResolver->resolve(is_object($entity) ? ClassUtils::getClass($entity) : $entity);
        $metadata = $this->getAllMetadata();

        if (isset($metadata[$class])) {
            return $metadata[$class];
        }
        if (isset($this->childMetadata[$class])) {
            return $this->childMetadata[$class];
        }

        $child = $class;

        while ($parent = get_parent_class($child)) {
            if (isset($metadata[$parent])) {
                $this->childMetadata[$class] = $metadata[$parent];

                return $this->childMetadata[$class];
            }

            $child = $parent;
        }

        throw new MetadataException(sprintf('Unable to get metadata for class "%s".', $class));
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     *
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getAllMetadata(): array
    {
        if (null === $this->metadata) {
            $metadata = null;

            if (null !== $this->cache) {
                $metadata = $this->cache->get(self::CACHE_KEY);
            }
            if (null === $metadata) {
                $metadata = $this->metadataPool->getAllMetadata();

                if (null !== $this->cache && !$this->cache->set(self::CACHE_KEY, $metadata)) {
                    throw new MetadataException('Unable to cache metadata.');
                }
            }

            $this->buildTree($metadata, array_keys($metadata));

            foreach ($metadata as $meta) {
                $this->eventDispatcher->dispatch(new MetadataEvent($meta), MetadataEvents::LOADED);
            }

            $this->metadata = $metadata;
        }

        return $this->metadata;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata[] $metadata Metadata
     * @param string[]                                $parents  Parent entity classes
     *
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    private function buildTree(array $metadata, array $parents): void
    {
        foreach ($parents as $parent) {
            $parent = $this->entityResolver->resolve($parent);

            $parentMeta = $metadata[$parent];

            $parentConfig = $parentMeta->getConfiguration();

            foreach ($parentConfig['children'] as $key => $child) {
                $child = $this->entityResolver->resolve($child);

                if (!isset($metadata[$child])) {
                    unset($parentConfig['children'][$key]);

                    continue;
                }

                $associated = false;
                $childMeta  = $metadata[$child];

                foreach ($childMeta->getMappings() as $property => $mapping) {
                    if (!$childMeta->isAssociation($property)
                        || ($mapping['targetEntity'] !== $parent && !in_array($mapping['targetEntity'], class_parents($parent)))
                    ) {
                        continue;
                    }

                    $childMeta->setParent(new AssociatedMetadata($property, $parentMeta));
                    $parentMeta->addChild(new AssociatedMetadata($property, $childMeta));

                    $associated = true;

                    if (null !== $mapping['inversedBy']) {
                        break;
                    }
                }
                if (!$associated) {
                    throw new MetadataException(
                        sprintf('Entity "%s" is not associated with entity "%s".', $child, $parent)
                    );
                }
            }

            $this->buildTree($metadata, $parentConfig['children']);
        }
    }
}
