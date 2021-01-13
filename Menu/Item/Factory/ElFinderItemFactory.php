<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
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
 * elFinder menu item factory
 */
class ElFinderItemFactory implements ItemFactoryInterface
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
        yield (new Item('elfinder'))
            ->setIndexTitle('elfinder.action.index.link')
            ->setIndexUrl($this->router->generate('elfinder', [
                'instance' => 'darvin_admin_ckeditor',
            ]))
            ->setPosition(1000)
            ->setAttr([
                'target' => '_blank',
            ]);
    }
}
