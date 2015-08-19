<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
