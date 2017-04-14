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

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ConfigBundle\Entity\ParameterEntity;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Configuration menu item factory
 */
class ConfigurationItemFactory implements ItemFactoryInterface
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
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->authorizationChecker->isGranted(Permission::EDIT, ParameterEntity::class)) {
            return [];
        }

        $item = (new Item('configuration'))
            ->setIndexTitle('configuration.action.edit.link')
            ->setIndexUrl($this->router->generate('darvin_admin_configuration'))
            ->setDescription('configuration.menu.description')
            ->setMainColor('#516fd0')
            ->setSidebarColor('#9a8efe')
            ->setMainIcon('bundles/darvinadmin/images/admin/configuration_main.png')
            ->setSidebarIcon('bundles/darvinadmin/images/admin/configuration_sidebar.png')
            ->setPosition(1000);

        return [
            $item,
        ];
    }
}
