<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * View widget generator
 */
interface WidgetGeneratorInterface
{
    /**
     * @param object $entity   Entity
     * @param array  $options  Options
     * @param string $property Property name
     *
     * @return string
     */
    public function generate($entity, array $options = array(), $property = null);

    /**
     * @return string
     */
    public function getAlias();
}
