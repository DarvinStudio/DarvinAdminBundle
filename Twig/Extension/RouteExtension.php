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
        return [
            new \Twig_SimpleFunction('admin_path', [$this->adminRouter, 'generate']),
            new \Twig_SimpleFunction('admin_route_exists', [$this->adminRouter, 'isRouteExists']),
            new \Twig_SimpleFunction('admin_url', [$this->adminRouter, 'generateAbsolute']),
        ];
    }
}
