<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Route Twig extension
 */
class RouteExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface $adminRouter Admin router
     */
    public function __construct(AdminRouterInterface $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): iterable
    {
        foreach ([
            'admin_path'         => 'generate',
            'admin_route_exists' => 'exists',
            'admin_url'          => 'generateAbsolute',
        ] as $name => $method) {
            yield new TwigFunction($name, [$this->adminRouter, $method]);
        }
    }
}
