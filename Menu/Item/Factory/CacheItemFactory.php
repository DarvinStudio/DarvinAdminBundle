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
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ConfigBundle\Entity\ParameterEntity;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Cache menu item factory
 */
class CacheItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $caches;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     * @param array                                                                        $caches               Array of caches
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, array $caches)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->caches = $caches;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        if ($this->authorizationChecker->isGranted(Permission::EDIT, ParameterEntity::class) && !empty($this->caches)) {
            yield (new Item('cache'))
                ->setIndexTitle('cache.action.clear.link')
                ->setIndexUrl($this->router->generate('darvin_admin_cache_clear'))
                ->setPosition(1100);
        }
    }
}
