<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu\Factory;

use Darvin\AdminBundle\Menu\Group;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;

/**
 * Menu group factory
 */
class GroupFactory implements ItemFactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config Configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        foreach ($this->config as $name => $attr) {
            yield (new Group($name))
                ->setAssociatedObject($attr['associated_object'])
                ->setPosition($attr['position']);
        }
    }
}
