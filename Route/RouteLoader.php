<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     */
    public function __construct(MetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $routeGenerator = new CrudRouteGenerator();

        foreach ($this->metadataManager->getAllMetadata() as $entityClass => $meta) {
            $routes->addCollection($routeGenerator->generate($entityClass, $meta));
        }

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
