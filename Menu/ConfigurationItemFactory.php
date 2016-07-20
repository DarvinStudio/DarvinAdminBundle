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

use Symfony\Component\Routing\RouterInterface;

/**
 * Configuration menu item factory
 */
class ConfigurationItemFactory implements ItemFactoryInterface
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
     * {@inheritdoc}
     */
    public function getItems()
    {
        $item = (new Item('configuration'))
            ->setIndexTitle('configuration.action.edit.link')
            ->setIndexUrl($this->router->generate('darvin_admin_configuration'))
            ->setDescription('configuration.menu.description')
            ->setMainColor('#5a4fb6')
            ->setMainIcon('bundles/darvinadmin/images/admin/configuration_main.png')
            ->setSidebarIcon('bundles/darvinadmin/images/admin/configuration_sidebar.png');

        return [
            $item,
        ];
    }
}
