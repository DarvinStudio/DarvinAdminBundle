<?php declare(strict_types=1);
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
 * Menu group
 */
class Group extends Item
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->indexTitle = sprintf('menu.group.%s.title', $name);
    }
}
