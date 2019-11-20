<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

/**
 * Menu group
 */
class Group extends Item
{
    /**
     * @param string      $name             Group name
     * @param string|null $associatedObject Associated object
     * @param int|null    $position         Position
     */
    public function __construct(string $name, ?string $associatedObject, ?int $position)
    {
        parent::__construct($name);

        $this->associatedObject = $associatedObject;
        $this->position = $position;

        $this->indexTitle = sprintf('menu.group.%s.title', $name);
    }
}
