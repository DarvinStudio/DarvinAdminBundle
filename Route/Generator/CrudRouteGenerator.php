<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
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
        [
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
                [
                    'get',
                    'post',
                ],
            ],
            AdminRouterInterface::TYPE_PREVIEW => [
                '%s_preview',
                '%s/{id}/preview',
                [
                    'id' => '\d+',
                ],
                [
                    'get',
                ],
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
                [
                    'get',
                    'post',
                ],
            ],
            AdminRouterInterface::TYPE_REPAGINATE => [
                '%s_repaginate',
                '%s/repaginate',
                [],
                [
                    'post',
                ]
            ],
            AdminRouterInterface::TYPE_INDEX => [
                '%s',
                '%s/',
                [],
                [
                    'get',
                ],
            ],
        ],
        [
            AdminRouterInterface::TYPE_UPDATE_PROPERTY => [
                '%s_update_property',
                '%s/update-property/{property}',
                [
                    'property' => '\w+',
                ],
                [
                    'post',
                ],
            ],
            AdminRouterInterface::TYPE_DELETE => [
                '%s_delete',
                '%s/delete',
                [],
                [
                    'post',
                ],
            ],
            AdminRouterInterface::TYPE_EDIT => [
                '%s_edit',
                '%s/edit',
                [],
                [
                    'get',
                    'post',
                ],
            ],
            AdminRouterInterface::TYPE_PREVIEW => [
                '%s_preview',
                '%s/preview',
                [],
                [
                    'get',
                ],
            ],
            AdminRouterInterface::TYPE_SHOW => [
                '%s_show',
                '%s/show',
                [],
                [
                    'get',
                ],
            ],
            AdminRouterInterface::TYPE_NEW => [
                '%s_new',
                '%s/new',
                [],
                [
                    'get',
                    'post',
                ],
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function generate(string $entityClass, Metadata $meta): RouteCollection
    {
        $routes = new RouteCollection();
        $config = $meta->getConfiguration();

        foreach (self::MODEL[$config['single_instance']] as $type => list($namePattern, $pathPattern, $requirements, $methods)) {
            if (in_array($type, $config['route_blacklist'])
                || (AdminRouterInterface::TYPE_NEW === $type && $meta->isEntityAbstract())
            ) {
                continue;
            }

            $route = new Route(
                sprintf($pathPattern, str_replace('_', '-', $meta->getEntityName())),
                [
                    '_controller' => sprintf('darvin_admin.crud.action.%s', str_replace('-', '_', $type)),
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
