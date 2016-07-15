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
     * @var \Darvin\AdminBundle\Menu\ItemFactory[]
     */
    private $itemFactories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->itemFactories = [];
    }

    /**
     * @param \Darvin\AdminBundle\Menu\ItemFactoryInterface $itemFactory Menu item factory
     *
     * @throws \Darvin\AdminBundle\Menu\MenuException
     */
    public function addItemFactory(ItemFactoryInterface $itemFactory)
    {
        $class = get_class($itemFactory);

        if (isset($this->itemFactories[$class])) {
            throw new MenuException(sprintf('Item factory "%s" already added to menu.', $class));
        }

        $this->itemFactories[$class] = $itemFactory;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = [];

        foreach ($this->itemFactories as $itemFactory) {
            $items = array_merge($items, $itemFactory->getItems());
        }

        return $items;
    }
}
