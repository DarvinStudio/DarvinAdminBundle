<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public function __construct(array $items = [])
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
