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

use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\MetadataException;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin router
 */
class AdminRouter
{
    const OPTION_ENTITY_CLASS = 'admin_entity_class';
    const OPTION_ROUTE_TYPE   = 'admin_route_type';

    const TYPE_BATCH_DELETE    = 'batch-delete';
    const TYPE_COPY            = 'copy';
    const TYPE_DELETE          = 'delete';
    const TYPE_EDIT            = 'edit';
    const TYPE_INDEX           = 'index';
    const TYPE_NEW             = 'new';
    const TYPE_SHOW            = 'show';
    const TYPE_UPDATE_PROPERTY = 'update-property';

    /**
     * @var array
     */
    private static $typesRequiringId = [
        self::TYPE_BATCH_DELETE,
        self::TYPE_COPY,
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_SHOW,
        self::TYPE_UPDATE_PROPERTY,
    ];

    /**
     * @var array
     */
    private static $typesRequiringParentId = [
        self::TYPE_BATCH_DELETE,
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_INDEX,
        self::TYPE_NEW,
        self::TYPE_SHOW,
    ];

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $genericRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @var array
     */
    private $routeNames;

    /**
     * @param \Symfony\Component\Routing\RouterInterface                  $genericRouter      Generic router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor             $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     */
    public function __construct(
        RouterInterface $genericRouter,
        IdentifierAccessor $identifierAccessor,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->genericRouter = $genericRouter;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;

        $this->initialized = false;
        $this->routeNames = [];
    }

    /**
     * @param object $entity      Entity
     * @param string $entityClass Entity class
     * @param string $routeType   Route type
     * @param array  $params      Parameters
     *
     * @return string
     */
    public function generateAbsolute($entity = null, $entityClass = null, $routeType = self::TYPE_SHOW, array $params = [])
    {
        return $this->generate($entity, $entityClass, $routeType, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param object $entity        Entity
     * @param string $entityClass   Entity class
     * @param string $routeType     Route type
     * @param array  $params        Parameters
     * @param mixed  $referenceType Reference type
     *
     * @return string
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    public function generate(
        $entity = null,
        $entityClass = null,
        $routeType = self::TYPE_SHOW,
        array $params = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        if (empty($entity) && empty($entityClass)) {
            throw new RouteException('Entity or entity class must be provided.');
        }
        if (empty($entityClass)) {
            $entityClass = ClassUtils::getClass($entity);
        }
        if (!$this->isRouteExists($entityClass, $routeType)) {
            throw new RouteException(
                sprintf('Route "%s" does not exist for entity "%s".', $routeType, $entityClass)
            );
        }

        $this->getAdditionalParams($params, $entityClass, $routeType, $entity);

        return $this->genericRouter->generate($this->getRouteName($entityClass, $routeType), $params, $referenceType);
    }

    /**
     * @param mixed  $objectOrClass Entity object or class
     * @param string $routeType     Route type
     *
     * @return bool
     */
    public function isRouteExists($objectOrClass, $routeType)
    {
        $routeName = $this->getRouteName(
            is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass,
            $routeType
        );

        return !empty($routeName);
    }

    /**
     * @param string $entityClass Entity class
     * @param string $routeType   Route type
     *
     * @return string
     */
    private function getRouteName($entityClass, $routeType)
    {
        $this->init();

        if (isset($this->routeNames[$entityClass][$routeType])) {
            return $this->routeNames[$entityClass][$routeType];
        }

        $child = $entityClass;

        while ($parent = get_parent_class($child)) {
            if (isset($this->routeNames[$parent][$routeType])) {
                $this->routeNames[$entityClass][$routeType] = $this->routeNames[$parent][$routeType];

                return $this->routeNames[$entityClass][$routeType];
            }

            $child = $parent;
        }

        return null;
    }

    /**
     * @param array  $params      Parameters
     * @param string $entityClass Entity class
     * @param string $routeType   Route type
     * @param object $entity      Entity
     *
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    private function getAdditionalParams(array &$params, $entityClass, $routeType, $entity = null)
    {
        if (in_array($routeType, self::$typesRequiringId) && !isset($params['id']) && !empty($entity)) {
            try {
                $params['id'] = $this->identifierAccessor->getValue($entity);
            } catch (MetadataException $ex) {
                throw new RouteException(
                    sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
                );
            }
        }

        $meta = $this->metadataManager->getMetadata($entityClass);

        if (!$meta->hasParent() || !in_array($routeType, self::$typesRequiringParentId)) {
            return;
        }

        $associationParam = $meta->getParent()->getAssociationParameterName();

        if (isset($params[$associationParam])) {
            return;
        }
        if (empty($entity)) {
            throw new RouteException(
                sprintf('Route "%s" for entity "%s" requires parameter "%s".', $routeType, $entityClass, $associationParam)
            );
        }

        $params[$associationParam] = $this->getParentEntityId($entity, $meta->getParent()->getAssociation(), $routeType);
    }

    /**
     * @param object $entity      Entity
     * @param string $association Association name
     * @param string $routeType   Route type
     *
     * @return int
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    private function getParentEntityId($entity, $association, $routeType)
    {
        if (!$this->propertyAccessor->isReadable($entity, $association)) {
            $message = sprintf(
                'Property "%s::$%s" required to generate URL or path for route "%s" is not readable.',
                ClassUtils::getClass($entity),
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

    private function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        /** @var \Symfony\Component\Routing\Route $route */
        foreach ($this->genericRouter->getRouteCollection() as $name => $route) {
            if (!$route->hasOption(self::OPTION_ENTITY_CLASS) || !$route->hasOption(self::OPTION_ROUTE_TYPE)) {
                continue;
            }

            $entityClass = $route->getOption(self::OPTION_ENTITY_CLASS);

            if (!isset($this->routeNames[$entityClass])) {
                $this->routeNames[$entityClass] = [];
            }

            $this->routeNames[$entityClass][$route->getOption(self::OPTION_ROUTE_TYPE)] = $name;
        }
    }
}
