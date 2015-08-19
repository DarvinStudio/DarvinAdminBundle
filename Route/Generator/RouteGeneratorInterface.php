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
