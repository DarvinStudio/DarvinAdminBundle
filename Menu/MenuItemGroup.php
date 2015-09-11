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
 * Menu item group
 */
class MenuItemGroup implements MenuItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private $items;

    /**
     * @param string                                       $name  Group name
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface[] $items Menu items
     */
    public function __construct($name, array $items = array())
    {
        $this->name = $name;
        $this->items = $items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item Menu item
     */
    public function addItem(MenuItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren(array $children)
    {
        $this->items = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuTitle()
    {
        return sprintf('menu.group.%s.title', $this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuDescription()
    {
        return sprintf('menu.group.%s.description', $this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedObjectClass()
    {
        return null;
    }
}
