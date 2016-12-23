<?php
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
use Darvin\AdminBundle\Route\AdminRouter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * CRUD route generator
 */
class CrudRouteGenerator implements RouteGeneratorInterface
{
    /**
     * @var array
     */
    private static $model = [
        AdminRouter::TYPE_UPDATE_PROPERTY => [
            '%s_update_property',
            '%s/{id}/update-property/{property}',
            '%s:updatePropertyAction',
            [
                'id'       => '\d+',
                'property' => '\w+',
            ],
            [
                'post',
            ],
        ],
        AdminRouter::TYPE_COPY => [
            '%s_copy',
            '%s/{id}/copy',
            '%s:copyAction',
            [
                'id' => '\d+',
            ],
            [
                'post',
            ],
        ],
        AdminRouter::TYPE_DELETE => [
            '%s_delete',
            '%s/{id}/delete',
            '%s:deleteAction',
            [
                'id' => '\d+',
            ],
            [
                'post',
            ],
        ],
        AdminRouter::TYPE_EDIT => [
            '%s_edit',
            '%s/{id}/edit',
            '%s:editAction',
            [
                'id' => '\d+',
            ],
            [],
        ],
        AdminRouter::TYPE_SHOW => [
            '%s_show',
            '%s/{id}/show',
            '%s:showAction',
            [
                'id' => '\d+',
            ],
            [
                'get',
            ],
        ],
        AdminRouter::TYPE_BATCH_DELETE => [
            '%s_batch_delete',
            '%s/batch-delete',
            '%s:batchDeleteAction',
            [],
            [
                'post',
            ],
        ],
        AdminRouter::TYPE_NEW => [
            '%s_new',
            '%s/new',
            '%s:newAction',
            [],
            [],
        ],
        AdminRouter::TYPE_INDEX => [
            '%s',
            '%s/',
            '%s:indexAction',
            [],
            [
                'get',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function generate($entityClass, Metadata $meta)
    {
        $routes = new RouteCollection();

        $configuration = $meta->getConfiguration();

        foreach (self::$model as $routeType => $attr) {
            if (in_array($routeType, $configuration['route_blacklist'])) {
                continue;
            }

            $route = new Route(
                sprintf($attr[1], str_replace('_', '-', $meta->getEntityName())),
                [
                    '_controller' => sprintf($attr[2], $meta->getControllerId()),
                ],
                $attr[3],
                [
                    AdminRouter::OPTION_ENTITY_CLASS => $entityClass,
                    AdminRouter::OPTION_ROUTE_TYPE   => $routeType,
                ],
                '',
                [],
                $attr[4]
            );

            $routes->add(sprintf($attr[0], $meta->getRoutingPrefix()), $route);
        }

        return $routes;
    }
}
