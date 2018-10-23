<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Route;

use Darvin\AdminBundle\Event\Router\RouteEvent;
use Darvin\AdminBundle\Event\Router\RouterEvents;
use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\MetadataException;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\Utils\ORM\EntityResolverInterface;
use Darvin\Utils\Routing\RouteManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin router
 */
class AdminRouter
{
    public const OPTION_ENTITY_CLASS = 'admin_entity_class';
    public const OPTION_ROUTE_TYPE   = 'admin_route_type';

    public const TYPE_BATCH_DELETE    = 'batch-delete';
    public const TYPE_COPY            = 'copy';
    public const TYPE_DELETE          = 'delete';
    public const TYPE_EDIT            = 'edit';
    public const TYPE_INDEX           = 'index';
    public const TYPE_NEW             = 'new';
    public const TYPE_SHOW            = 'show';
    public const TYPE_UPDATE_PROPERTY = 'update-property';

    protected const REQUIRE_ID = [
        self::TYPE_COPY,
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_SHOW,
        self::TYPE_UPDATE_PROPERTY,
    ];

    protected const REQUIRE_PARENT_ID = [
        self::TYPE_BATCH_DELETE,
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_INDEX,
        self::TYPE_NEW,
        self::TYPE_SHOW,
    ];

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    protected $entityResolver;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $genericRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    protected $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    protected $routeManager;

    /**
     * @var array|null
     */
    protected $routeNames;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                   $entityResolver     Entity resolver
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher    Event dispatcher
     * @param \Symfony\Component\Routing\RouterInterface                  $genericRouter      Generic router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor             $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     * @param \Darvin\Utils\Routing\RouteManagerInterface                 $routeManager       Route manager
     */
    public function __construct(
        EntityResolverInterface $entityResolver,
        EventDispatcherInterface $eventDispatcher,
        RouterInterface $genericRouter,
        IdentifierAccessor $identifierAccessor,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        RouteManagerInterface $routeManager
    ) {
        $this->entityResolver = $entityResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->genericRouter = $genericRouter;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->routeManager = $routeManager;

        $this->routeNames = null;
    }

    /**
     * @param object $entity    Entity
     * @param string $class     Entity class
     * @param string $routeType Route type
     * @param array  $params    Parameters
     *
     * @return string
     */
    public function generateAbsolute($entity = null, ?string $class = null, string $routeType = self::TYPE_SHOW, array $params = []): string
    {
        return $this->generate($entity, $class, $routeType, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param object $entity        Entity
     * @param string $class         Entity class
     * @param string $routeType     Route type
     * @param array  $params        Parameters
     * @param mixed  $referenceType Reference type
     *
     * @return string
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    public function generate(
        $entity = null,
        ?string $class = null,
        string $routeType = self::TYPE_SHOW,
        array $params = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if (empty($entity) && empty($class)) {
            throw new RouteException('Entity or entity class must be provided.');
        }
        if (empty($class)) {
            $class = get_class($entity);
        }
        if (!$this->exists($class, $routeType)) {
            throw new RouteException(
                sprintf('Route "%s" does not exist for entity "%s".', $routeType, $class)
            );
        }

        $class = $this->entityResolver->resolve($class);

        $name = $this->getRouteName($class, $routeType);

        $this->collectAdditionalParams($params, $class, $routeType, $entity);

        $event = new RouteEvent($name, $routeType, $params, $referenceType, $entity, $class);

        $this->eventDispatcher->dispatch(RouterEvents::PRE_GENERATE, $event);

        return $this->genericRouter->generate($name, $event->getParams(), $event->getReferenceType());
    }

    /**
     * @param object|string $entity    Entity
     * @param string        $routeType Route type
     *
     * @return bool
     */
    public function exists($entity, string $routeType): bool
    {
        $name = $this->getRouteName($this->entityResolver->resolve(is_object($entity) ? get_class($entity) : $entity), $routeType);

        return !empty($name);
    }

    /**
     * @param string $class     Entity class
     * @param string $routeType Route type
     *
     * @return string
     */
    final protected function getRouteName(string $class, string $routeType): string
    {
        $names = $this->getRouteNames();

        if (isset($names[$class][$routeType])) {
            return $names[$class][$routeType];
        }

        $child = $class;

        while ($parent = get_parent_class($child)) {
            if (isset($names[$parent][$routeType])) {
                if (!isset($this->routeNames[$class])) {
                    $this->routeNames[$class] = [];
                }

                $this->routeNames[$class][$routeType] = $names[$parent][$routeType];

                return $names[$parent][$routeType];
            }

            $child = $parent;
        }

        return null;
    }

    /**
     * @param array  $params    Parameters
     * @param string $class     Entity class
     * @param string $routeType Route type
     * @param object $entity    Entity
     *
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    final protected function collectAdditionalParams(array &$params, string $class, string $routeType, $entity = null): void
    {
        if (in_array($routeType, self::REQUIRE_ID) && !isset($params['id']) && !empty($entity)) {
            try {
                $params['id'] = $this->identifierAccessor->getValue($entity);
            } catch (MetadataException $ex) {
                throw new RouteException(
                    sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
                );
            }
        }

        $meta = $this->metadataManager->getMetadata($class);

        if (!$meta->hasParent() || !in_array($routeType, self::REQUIRE_PARENT_ID)) {
            return;
        }

        $associationParam = $meta->getParent()->getAssociationParameterName();

        if (isset($params[$associationParam])) {
            return;
        }
        if (empty($entity)) {
            throw new RouteException(
                sprintf('Route "%s" for entity "%s" requires parameter "%s".', $routeType, $class, $associationParam)
            );
        }

        $params[$associationParam] = $this->getParentId($entity, $meta->getParent()->getAssociation(), $routeType);
    }

    /**
     * @param object $entity      Entity
     * @param string $association Association name
     * @param string $routeType   Route type
     *
     * @return int
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    final protected function getParentId($entity, string $association, string $routeType)
    {
        if (!$this->propertyAccessor->isReadable($entity, $association)) {
            $message = sprintf(
                'Property "%s::$%s" required to generate URL or path for route "%s" is not readable.',
                get_class($entity),
                $association,
                $routeType
            );

            throw new RouteException($message);
        }

        $parentEntity = $this->propertyAccessor->getValue($entity, $association);

        try {
            return $this->identifierAccessor->getValue($parentEntity);
        } catch (MetadataException $ex) {
            throw new RouteException(
                sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
            );
        }
    }

    /**
     * @return array
     */
    final protected function getRouteNames(): array
    {
        if (null === $this->routeNames) {
            $this->routeNames = [];

            foreach ($this->routeManager->getNames() as $name) {
                if (!$this->routeManager->hasOption($name, self::OPTION_ENTITY_CLASS)
                    || !$this->routeManager->hasOption($name, self::OPTION_ROUTE_TYPE)
                ) {
                    continue;
                }

                $class = $this->routeManager->getOption($name, self::OPTION_ENTITY_CLASS);

                if (!isset($this->routeNames[$class])) {
                    $this->routeNames[$class] = [];
                }

                $this->routeNames[$class][$this->routeManager->getOption($name, self::OPTION_ROUTE_TYPE)] = $name;
            }
        }

        return $this->routeNames;
    }
}
