<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 17:08
 * To change this template use File | Settings | File Templates.
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
     * @var \Twig_Environment
     */
    private $environment;

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
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('admin_menu', array($this, 'renderMenu'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @param string $template Template
     *
     * @return string
     */
    public function renderMenu($template = 'DarvinAdminBundle::menu.html.twig')
    {
        return $this->environment->render($template, array(
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
