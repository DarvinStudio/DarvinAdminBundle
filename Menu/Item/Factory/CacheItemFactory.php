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

use Darvin\AdminBundle\Cache\CacheClearerInterface;
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
     * @var \Darvin\AdminBundle\Cache\CacheClearerInterface|null
     */
    private $cacheClearer;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
    }

    /**
     * @param \Darvin\AdminBundle\Cache\CacheClearerInterface $cacheClearer
     */
    public function setCacheClearer(?CacheClearerInterface $cacheClearer): void
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): iterable
    {
        if ($this->authorizationChecker->isGranted(Permission::EDIT, ParameterEntity::class)
            && null !== $this->cacheClearer && $this->cacheClearer->hasCommands('list')) {
            yield (new Item('cache'))
                ->setIndexTitle('cache.action.clear.link')
                ->setIndexUrl($this->router->generate('darvin_admin_cache_clear'))
                ->setPosition(1100);
        }
    }
}
