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
class ItemGroup extends Item
{
    /**
     * @param string $name             Name
     * @param int    $position         Position
     * @param string $visualAssetsPath Visual assets path
     */
    public function __construct($name, $position, $visualAssetsPath)
    {
        parent::__construct($name);

        $this->indexTitle = sprintf('menu.group.%s.title', $name);
        $this->description = sprintf('menu.group.%s.description', $name);
        $this->mainIcon = sprintf('%s/images/main_menu_%s.png', $visualAssetsPath, $name);
        $this->sidebarIcon = sprintf('%s/images/left_menu_%s.png', $visualAssetsPath, $name);
        $this->position = $position;
    }
}
