<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 14:42
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Util;

/**
 * Strings utility
 */
class StringsUtil
{
    /**
     * @param string $string String
     *
     * @return string
     */
    public static function toUnderscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}
