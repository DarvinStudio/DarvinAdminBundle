<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Route\Generator;

use Darvin\AdminBundle\Metadata\Metadata;

/**
 * Route generator
 */
interface RouteGeneratorInterface
{
    /**
     * @param string                                $entityClass Entity class
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta        Metadata
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function generate($entityClass, Metadata $meta);
}
