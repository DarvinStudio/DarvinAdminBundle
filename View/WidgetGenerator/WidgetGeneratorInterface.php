<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 10:03
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * View widget generator
 */
interface WidgetGeneratorInterface
{
    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return string
     */
    public function generate($entity, array $options = array());

    /**
     * @return string
     */
    public function getAlias();
}
