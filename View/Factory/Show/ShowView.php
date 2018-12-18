<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Show;

/**
 * Show view
 */
class ShowView
{
    /**
     * @var \Darvin\AdminBundle\View\Factory\Show\Item[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Factory\Show\Item[] $items Items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Show\Item $item Item
     */
    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Show\Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
