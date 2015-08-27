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
    private static $model = array(
        AdminRouter::TYPE_UPDATE_PROPERTY => array(
            '%s_update_property',
            '%s/{id}/update-property/{property}',
            '%s:updatePropertyAction',
            array(
                'id'       => '\d+',
                'property' => '\w+',
            ),
            array(
                'post',
            ),
        ),
        AdminRouter::TYPE_COPY => array(
            '%s_copy',
            '%s/{id}/copy',
            '%s:copyAction',
            array(
                'id' => '\d+',
            ),
            array(
                'post',
            ),
        ),
        AdminRouter::TYPE_DELETE => array(
            '%s_delete',
            '%s/{id}/delete',
            '%s:deleteAction',
            array(
                'id' => '\d+',
            ),
            array(
                'post',
            ),
        ),
        AdminRouter::TYPE_EDIT => array(
            '%s_edit',
            '%s/{id}/edit',
            '%s:editAction',
            array(
                'id' => '\d+',
            ),
            array(),
        ),
        AdminRouter::TYPE_SHOW => array(
            '%s_show',
            '%s/{id}/show',
            '%s:showAction',
            array(
                'id' => '\d+',
            ),
            array(
                'get',
            ),
        ),
        AdminRouter::TYPE_NEW => array(
            '%s_new',
            '%s/new',
            '%s:newAction',
            array(),
            array(),
        ),
        AdminRouter::TYPE_INDEX => array(
            '%s',
            '%s/',
            '%s:indexAction',
            array(),
            array(
                'get',
            ),
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function generate($entityClass, Metadata $meta)
    {
        $routes = new RouteCollection();

        $configuration = $meta->getConfiguration();

        foreach (self::$model as $routeType => $attr) {
            if (in_array($routeType, $configuration['disabled_routes'])) {
                continue;
            }

            $route = new Route(
                sprintf($attr[1], $meta->getEntityName()),
                array(
                    '_controller' => sprintf($attr[2], $meta->getControllerId()),
                ),
                $attr[3],
                array(
                    AdminRouter::OPTION_ENTITY_CLASS => $entityClass,
                    AdminRouter::OPTION_ROUTE_TYPE   => $routeType,
                ),
                '',
                array(),
                $attr[4]
            );

            $routes->add(sprintf($attr[0], $meta->getRoutingPrefix()), $route);
        }

        return $routes;
    }
}
