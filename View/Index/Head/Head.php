<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 11:24
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
    public function __construct(array $items = array())
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
