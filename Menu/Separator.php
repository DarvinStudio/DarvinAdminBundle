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
 * Menu separator
 */
class Separator extends Item
{
    /**
     * @param string $name      Separator name
     * @param string $groupName Group name
     * @param int    $position  Position
     */
    public function __construct(string $name, string $groupName, int $position)
    {
        parent::__construct(implode('_', [$groupName, $name]));

        $this->parentName = $groupName;
        $this->position = $position;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return false;
    }
}
