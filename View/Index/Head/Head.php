<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Index\Head;

/**
 * Index view head
 */
class Head
{
    /**
     * @var \Darvin\AdminBundle\View\Index\Head\HeadItem[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Index\Head\HeadItem[] $items Items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param string                                       $field Field name
     * @param \Darvin\AdminBundle\View\Index\Head\HeadItem $item  Item
     *
     * @return Head
     */
    public function addItem($field, HeadItem $item)
    {
        $this->items[$field] = $item;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Head\HeadItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
