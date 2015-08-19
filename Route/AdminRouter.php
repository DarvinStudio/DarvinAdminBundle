<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 16:12
 * To change this template use File | Settings | File Templates.
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

    const TYPE_DELETE          = 'delete';
    const TYPE_EDIT            = 'edit';
    const TYPE_INDEX           = 'index';
    const TYPE_NEW             = 'new';
    const TYPE_SHOW            = 'show';
    const TYPE_UPDATE_PROPERTY = 'update-property';

    /**
     * @var array
     */
    private static $typesRequiringId = array(
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_SHOW,
        self::TYPE_UPDATE_PROPERTY,
    );

    /**
     * @var array
     */
    private static $typesRequiringParentId = array(
        self::TYPE_DELETE,
        self::TYPE_EDIT,
        self::TYPE_INDEX,
        self::TYPE_NEW,
        self::TYPE_SHOW,
    );

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
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @var array
     */
    private $routeNames;

    /**
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor             $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     * @param \Symfony\Component\Routing\RouterInterface                  $router             Router
     */
    public function __construct(
        IdentifierAccessor $identifierAccessor,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        RouterInterface $router
    ) {
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->router = $router;
        $this->initialized = false;
        $this->routeNames = array();
    }

    /**
     * @param mixed  $classOrObject Entity class or object
     * @param string $routeType     Route type
     * @param array  $parameters    Parameters
     * @param mixed  $referenceType Reference type
     *
     * @return string
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    public function generate(
        $classOrObject,
        $routeType = self::TYPE_SHOW,
        array $parameters = array(),
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        $this->init();

        $isObject = is_object($classOrObject);

        $entityClass = $isObject ? ClassUtils::getClass($classOrObject) : $classOrObject;

        if (!isset($this->routeNames[$entityClass][$routeType])) {
            throw new RouteException(
                sprintf('Route "%s" does not exist for entity "%s".', $routeType, $entityClass)
            );
        }

        $this->getAdditionalParameters($parameters, $entityClass, $routeType, $isObject ? $classOrObject : null);

        return $this->router->generate($this->routeNames[$entityClass][$routeType], $parameters, $referenceType);
    }

    /**
     * @param mixed  $classOrObject Entity class or object
     * @param string $routeType     Route type
     *
     * @return bool
     */
    public function isRouteExists($classOrObject, $routeType)
    {
        $this->init();

        $entityClass = is_object($classOrObject) ? ClassUtils::getClass($classOrObject) : $classOrObject;

        return isset($this->routeNames[$entityClass][$routeType]);
    }

    /**
     * @param array  $parameters  Parameters
     * @param string $entityClass Entity class
     * @param string $routeType   Route type
     * @param object $entity      Entity
     *
     * @throws \Darvin\AdminBundle\Route\RouteException
     */
    private function getAdditionalParameters(array &$parameters, $entityClass, $routeType, $entity = null)
    {
        if (in_array($routeType, self::$typesRequiringId) && !isset($parameters['id']) && !empty($entity)) {
            try {
                $parameters['id'] = $this->identifierAccessor->getValue($entity);
            } catch (MetadataException $ex) {
                throw new RouteException(
                    sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
                );
            }
        }

        $meta = $this->metadataManager->getByEntityClass($entityClass);

        if (!$meta->hasParent() || !in_array($routeType, self::$typesRequiringParentId)) {
            return;
        }

        $association = $meta->getParent()->getAssociation();

        if (isset($parameters[$association])) {
            return;
        }
        if (empty($entity)) {
            throw new RouteException(
                sprintf('Route "%s" for entity "%s" requires parameter "%s".', $routeType, $entityClass, $association)
            );
        }

        $parameters[$association] = $this->getParentEntityId($entity, $association, $routeType);
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
        /** @var \Symfony\Component\Routing\Route $route */
        foreach ($this->router->getRouteCollection() as $name => $route) {
            if (!$route->hasOption(self::OPTION_ENTITY_CLASS) || !$route->hasOption(self::OPTION_ROUTE_TYPE)) {
                continue;
            }

            $entityClass = $route->getOption(self::OPTION_ENTITY_CLASS);

            if (!isset($this->routeNames[$entityClass])) {
                $this->routeNames[$entityClass] = array();
            }

            $this->routeNames[$entityClass][$route->getOption(self::OPTION_ROUTE_TYPE)] = $name;
        }

        $this->initialized = true;
    }
}
