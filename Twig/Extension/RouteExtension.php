<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 05.08.15
 * Time: 9:37
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Route\AdminRouter;

/**
 * Route Twig extension
 */
class RouteExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     */
    public function __construct(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('admin_path', array($this->adminRouter, 'generate')),
            new \Twig_SimpleFunction('admin_route_exists', array($this->adminRouter, 'isRouteExists')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_route_extension';
    }
}
