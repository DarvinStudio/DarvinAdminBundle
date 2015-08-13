<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 10:18
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Route;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\Generator\CrudRouteGenerator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route loader
 */
class RouteLoader extends Loader
{
    const RESOURCE_TYPE = 'darvin_admin';

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var bool
     */
    private $loaded;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     */
    public function __construct(MetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
        $this->loaded = false;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new RouteException(sprintf('Do not add route loader "%s" twice.', self::RESOURCE_TYPE));
        }

        $routes = new RouteCollection();

        $routeGenerator = new CrudRouteGenerator();

        foreach ($this->metadataManager->getAll() as $entityClass => $meta) {
            $routes->addCollection($routeGenerator->generate($entityClass, $meta));
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return self::RESOURCE_TYPE === $type;
    }
}
