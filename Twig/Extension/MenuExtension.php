<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Menu\Menu;

/**
 * Menu Twig extension
 */
class MenuExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Menu\Menu
     */
    private $menu;

    /**
     * @param \Darvin\AdminBundle\Menu\Menu $menu Menu
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'admin_menu',
                array($this, 'renderMenu'),
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true,
                )
            ),
        );
    }

    /**
     * @param \Twig_Environment $environment Twig environment
     * @param string            $template    Template
     *
     * @return string
     */
    public function renderMenu(\Twig_Environment $environment, $template = 'DarvinAdminBundle::menu.html.twig')
    {
        return $environment->render($template, array(
            'items' => $this->menu->getItems(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_menu_extension';
    }
}
