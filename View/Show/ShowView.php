<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 12:31
 */

namespace Darvin\AdminBundle\View\Show;

/**
 * Show view
 */
class ShowView
{
    /**
     * @var \Darvin\AdminBundle\View\Show\Item[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Show\Item[] $items Items
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    /**
     * @param \Darvin\AdminBundle\View\Show\Item $item Item
     *
     * @return ShowView
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Show\Item[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
