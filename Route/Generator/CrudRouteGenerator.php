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
            'darvin_admin.crud.action.update_property',
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
            'darvin_admin.crud.action.copy',
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
            'darvin_admin.crud.action.delete',
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
            'darvin_admin.crud.action.edit',
            [
                'id' => '\d+',
            ],
            [
                'get',
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_SHOW => [
            '%s_show',
            '%s/{id}/show',
            'darvin_admin.crud.action.show',
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
            'darvin_admin.crud.action.batch_delete',
            [],
            [
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_NEW => [
            '%s_new',
            '%s/new',
            'darvin_admin.crud.action.new',
            [],
            [
                'get',
                'post',
            ],
        ],
        AdminRouterInterface::TYPE_INDEX => [
            '%s',
            '%s/',
            'darvin_admin.crud.action.index',
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

        foreach (self::MODEL as $type => list($namePattern, $pathPattern, $controller, $requirements, $methods)) {
            if (in_array($type, $config['route_blacklist'])) {
                continue;
            }

            $route = new Route(
                sprintf($pathPattern, str_replace('_', '-', $meta->getEntityName())),
                [
                    '_controller' => $controller,
                ],
                $requirements,
                [
                    AdminRouterInterface::OPTION_ENTITY_CLASS => $entityClass,
                    AdminRouterInterface::OPTION_ROUTE_TYPE   => $type,
                ],
                '',
                [],
                $methods
            );

            $routes->add(sprintf($namePattern, $meta->getRoutingPrefix()), $route);
        }

        return $routes;
    }
}
