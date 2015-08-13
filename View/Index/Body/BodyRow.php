<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 11:18
 */

namespace Darvin\AdminBundle\View\Index\Body;

/**
 * Index view body row
 */
class BodyRow
{
    /**
     * @var \Darvin\AdminBundle\View\Index\Body\BodyRowItem[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRowItem[] $items Row items
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    /**
     * @param string                                          $field Field name
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRowItem $item  Row item
     *
     * @return BodyRow
     */
    public function addItem($field, BodyRowItem $item)
    {
        $this->items[$field] = $item;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Body\BodyRowItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
