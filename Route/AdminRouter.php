<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Route;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
use Darvin\AdminBundle\Metadata\MetadataException;
use Darvin\Utils\ORM\EntityResolverInterface;
use Darvin\Utils\Routing\RouteManagerInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin router
 */
class AdminRouter implements AdminRouterInterface
{
    private const REQUIRE_ID = [
        AdminRouterInterface::TYPE_COPY,
        AdminRouterInterface::TYPE_DELETE,
        AdminRouterInterface::TYPE_EDIT,
        AdminRouterInterface::TYPE_SHOW,
        AdminRouterInterface::TYPE_UPDATE_PROPERTY,
    ];

    private const REQUIRE_PARENT_ID = [
        AdminRouterInterface::TYPE_BATCH_DELETE,
        AdminRouterInterface::TYPE_DELETE,
        AdminRouterInterface::TYPE_EDIT,
        AdminRouterInterface::TYPE_INDEX,
        AdminRouterInterface::TYPE_NEW,
        AdminRouterInterface::TYPE_SHOW,
    ];

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $genericRouter;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface
     */
    private $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    private $routeManager;

    /**
     * @var array|null
     */
    private $routeNames;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                   $entityResolver     Entity resolver
     * @param \Symfony\Component\Routing\RouterInterface                  $genericRouter      Generic router
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface    $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager    Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     * @param \Symfony\Component\HttpFoundation\RequestStack              $requestStack       Request stack
     * @param \Darvin\Utils\Routing\RouteManagerInterface                 $routeManager       Route manager
     */
    public function __construct(
        EntityResolverInterface $entityResolver,
        RouterInterface $genericRouter,
        IdentifierAccessorInterface $identifierAccessor,
        AdminMetadataManagerInterface $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        RequestStack $requestStack,
        RouteManagerInterface $routeManager
    ) {
        $this->entityResolver = $entityResolver;
        $this->genericRouter = $genericRouter;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->requestStack = $requestStack;
        $this->routeManager = $routeManager;

        $this->routeNames = null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateAbsolute($entity = null, ?string $class = null, string $routeType = AdminRouterInterface::TYPE_SHOW, array $params = [], bool $preserveFilter = true): string
    {
        return $this->generate($entity, $class, $routeType, $params, UrlGeneratorInterface::ABSOLUTE_URL, $preserveFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(
        $entity = null,
        ?string $class = null,
        string $routeType = AdminRouterInterface::TYPE_SHOW,
        array $params = [],
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        bool $preserveFilter = true
    ): string {
        if (empty($entity) && empty($class)) {
            throw new \InvalidArgumentException('Entity or entity class must be provided.');
        }
        if (empty($class)) {
            $class = ClassUtils::getClass($entity);
        }
        if (!$this->exists($class, $routeType)) {
            throw new \InvalidArgumentException(
                sprintf('Route "%s" does not exist for entity "%s".', $routeType, $class)
            );
        }

        $class = $this->entityResolver->resolve($class);

        $name = $this->getRouteName($class, $routeType);

        $params = array_merge($params, $this->getExtraParams($params, $class, $routeType, $entity));

        if ($preserveFilter) {
            $params = array_merge($params, $this->getFilterParams($params, $class));
        }

        return $this->genericRouter->generate($name, $params, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($entity, string $routeType): bool
    {
        $name = $this->getRouteName($this->entityResolver->resolve(is_object($entity) ? ClassUtils::getClass($entity) : $entity), $routeType);

        return !empty($name);
    }

    /**
     * @param string $class     Entity class
     * @param string $routeType Route type
     *
     * @return string
     */
    private function getRouteName(string $class, string $routeType): ?string
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
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function getExtraParams(array $params, string $class, string $routeType, $entity = null): array
    {
        $extra = [];

        if (in_array($routeType, self::REQUIRE_ID) && !isset($params['id']) && !empty($entity)) {
            try {
                $extra['id'] = $this->identifierAccessor->getId($entity);
            } catch (MetadataException $ex) {
                throw new \RuntimeException(
                    sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
                );
            }
        }

        $meta = $this->metadataManager->getMetadata($class);

        if (!$meta->hasParent() || !in_array($routeType, self::REQUIRE_PARENT_ID)) {
            return $extra;
        }

        $associationParam = $meta->getParent()->getAssociationParameterName();

        if (isset($params[$associationParam])) {
            return $extra;
        }
        if (empty($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Route "%s" for entity "%s" requires parameter "%s".', $routeType, $class, $associationParam)
            );
        }

        $extra[$associationParam] = $this->getParentId($entity, $meta->getParent()->getAssociation(), $routeType);

        return $extra;
    }

    /**
     * @param array  $params Parameters
     * @param string $class  Entity class
     *
     * @return array
     */
    private function getFilterParams(array $params, string $class): array
    {
        $filterParams = [];
        $request      = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return $filterParams;
        }

        $param = $this->metadataManager->getMetadata($class)->getFilterFormTypeName();

        if ((isset($params[$param]) && is_array($params[$param])) || !$request->query->has($param)) {
            return $filterParams;
        }

        $filterData = $request->query->get($param);

        if (is_array($filterData)) {
            $filterParams[$param] = $filterData;
        }

        return $filterParams;
    }

    /**
     * @param object $entity      Entity
     * @param string $association Association name
     * @param string $routeType   Route type
     *
     * @return mixed
     * @throws \RuntimeException
     */
    private function getParentId($entity, string $association, string $routeType)
    {
        if (!$this->propertyAccessor->isReadable($entity, $association)) {
            $message = sprintf(
                'Property "%s::$%s" required to generate URL or path for route "%s" is not readable.',
                ClassUtils::getClass($entity),
                $association,
                $routeType
            );

            throw new \RuntimeException($message);
        }

        $parent = $this->propertyAccessor->getValue($entity, $association);

        if (empty($parent)) {
            return null;
        }
        try {
            return $this->identifierAccessor->getId($parent);
        } catch (MetadataException $ex) {
            throw new \RuntimeException(
                sprintf('Unable to generate URL or path for route "%s": "%s".', $routeType, $ex->getMessage())
            );
        }
    }

    /**
     * @return array
     */
    private function getRouteNames(): array
    {
        if (null === $this->routeNames) {
            $this->routeNames = [];

            foreach ($this->routeManager->getNames() as $name) {
                if (!$this->routeManager->hasOption($name, AdminRouterInterface::OPTION_ENTITY_CLASS)
                    || !$this->routeManager->hasOption($name, AdminRouterInterface::OPTION_ROUTE_TYPE)
                ) {
                    continue;
                }

                $class = $this->routeManager->getOption($name, AdminRouterInterface::OPTION_ENTITY_CLASS);

                if (!isset($this->routeNames[$class])) {
                    $this->routeNames[$class] = [];
                }

                $this->routeNames[$class][$this->routeManager->getOption($name, AdminRouterInterface::OPTION_ROUTE_TYPE)] = $name;
            }
        }

        return $this->routeNames;
    }
}
