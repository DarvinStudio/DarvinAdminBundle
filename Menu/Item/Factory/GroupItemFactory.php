<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu\Item\Factory;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;

/**
 * Group menu item factory
 */
class GroupItemFactory implements ItemFactoryInterface
{
    /**
     * @var array
     */
    private $groupsConfig;

    /**
     * @param array $groupsConfig Menu groups configuration
     */
    public function __construct(array $groupsConfig)
    {
        $this->groupsConfig = $groupsConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        foreach ($this->groupsConfig as $name => $config) {
            yield (new Item($name))
                ->setAssociatedObject($config['associated_object'])
                ->setIndexTitle(sprintf('menu.group.%s.title', $name))
                ->setPosition($config['position']);
        }
    }
}
