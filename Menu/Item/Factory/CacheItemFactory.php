<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu\Item\Factory;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * List cache clear menu item factory
 */
class CacheItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router Router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        yield (new Item('cache'))
            ->setIndexTitle('cache.action.clear.link')
            ->setIndexUrl($this->router->generate('darvin_admin_cache_clear'))
            ->setPosition(1100);
    }
}
