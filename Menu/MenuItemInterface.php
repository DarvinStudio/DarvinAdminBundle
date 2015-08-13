<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 15:51
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Menu;

/**
 * Menu item
 */
interface MenuItemInterface
{
    /**
     * @return string
     */
    public function getIndexUrl();

    /**
     * @return string
     */
    public function getNewUrl();

    /**
     * @return string
     */
    public function getMenuTitle();
}
