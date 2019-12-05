<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_path', [$this->adminRouter, 'generate']),
            new TwigFunction('admin_route_exists', [$this->adminRouter, 'exists']),
            new TwigFunction('admin_url', [$this->adminRouter, 'generateAbsolute']),
        ];
    }
}
