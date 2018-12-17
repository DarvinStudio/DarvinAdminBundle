<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Route\Generator;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * CRUD route generator
 */
class CrudRouteGenerator implements RouteGeneratorInterface
{
    private const MODEL = [
        AdminRouterInterface::TYPE_UPDATE_PROPERTY => [
            '%s_update_property',
            '%s/{id}/update-property/{property}',
            [
                'id'       => '\d+',
                'property' => '\w+',
            ],
            [
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_COPY => [
            '%s_copy',
            '%s/{id}/copy',
            [
                'id' => '\d+',
            ],
            [
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_DELETE => [
            '%s_delete',
            '%s/{id}/delete',
            [
                'id' => '\d+',
            ],
            [
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_EDIT => [
            '%s_edit',
            '%s/{id}/edit',
            [
                'id' => '\d+',
            ],
            [],
        ],
        AdminRouterInterface::TYPE_SHOW => [
            '%s_show',
            '%s/{id}/show',
            [
                'id' => '\d+',
            ],
            [
                'get',
            ],
        ],
        AdminRouterInterface::TYPE_BATCH_DELETE => [
            '%s_batch_delete',
            '%s/batch-delete',
            [],
            [
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_NEW => [
            '%s_new',
            '%s/new',
            [],
            [],
        ],
        AdminRouterInterface::TYPE_INDEX => [
            '%s',
            '%s/',
            [],
            [
                'get',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function generate(string $entityClass, Metadata $meta): RouteCollection
    {
        $routes = new RouteCollection();
        $config = $meta->getConfiguration();

        foreach (self::MODEL as $routeType => $attr) {
            if (in_array($routeType, $config['route_blacklist'])) {
                continue;
            }

            $route = new Route(
                sprintf($attr[1], str_replace('_', '-', $meta->getEntityName())),
                [
                    '_controller' => $meta->getControllerId(),
                    'name'        => $routeType,
                ],
                $attr[2],
                [
                    AdminRouterInterface::OPTION_ENTITY_CLASS => $entityClass,
                    AdminRouterInterface::OPTION_ROUTE_TYPE   => $routeType,
                ],
                '',
                [],
                $attr[3]
            );

            $routes->add(sprintf($attr[0], $meta->getRoutingPrefix()), $route);
        }

        return $routes;
    }
}
