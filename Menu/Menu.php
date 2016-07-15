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
     * @var \Darvin\AdminBundle\Menu\ItemFactoryInterface[]
     */
    private $itemFactories;

    /**
     * @var \Darvin\AdminBundle\Menu\Item[]
     */
    private $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->itemFactories = [];
        $this->items = null;
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
     * @return \Darvin\AdminBundle\Menu\Item[]
     *
     * @throws \Darvin\AdminBundle\Menu\MenuException
     */
    public function getItems()
    {
        if (null === $this->items) {
            /** @var \Darvin\AdminBundle\Menu\Item[] $items */
            $items = [];

            foreach ($this->itemFactories as $itemFactory) {
                foreach ($itemFactory->getItems() as $item) {
                    if (isset($items[$item->getName()])) {
                        throw new MenuException(sprintf('Menu item "%s" already exists.', $item->getName()));
                    }

                    $items[$item->getName()] = $item;
                }
            }

            $this->sortItems($items);

            foreach ($items as $item) {
                if (!$item->hasParent()) {
                    continue;
                }

                $parentName = $item->getParentName();

                if (!isset($items[$parentName])) {
                    $items[$parentName] = (new Item($parentName, sprintf('menu.group.%s.title', $parentName)))
                        ->setPosition($item->getPosition());
                }

                $parent = $items[$parentName];
                $parent->addChild($item);
            }
            foreach ($items as $key => $item) {
                if ($item->hasParent()) {
                    unset($items[$key]);
                }
            }

            $this->sortItems($items);

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item[] $items Menu items
     */
    private function sortItems(array &$items)
    {
        $defaultPos = max(array_map(function (Item $item) {
            return $item->getPosition();
        }, $items)) + 1;

        usort($items, function (Item $a, Item $b) use ($defaultPos) {
            $posA = null !== $a->getPosition() ? $a->getPosition() : $defaultPos;
            $posB = null !== $b->getPosition() ? $b->getPosition() : $defaultPos;

            return $posA === $posB ? 0 : ($posA > $posB ? 1 : -1);
        });
    }
}
