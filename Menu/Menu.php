<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 15:53
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Menu;

/**
 * Menu
 */
class Menu
{
    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = array();
    }

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item Menu item
     */
    public function addItem(MenuItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
