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
            new \Twig_SimpleFunction('admin_url', array($this->adminRouter, 'generateAbsolute')),
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
