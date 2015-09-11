<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dashboard;

use Darvin\AdminBundle\Route\AdminRouter;
use Symfony\Component\Templating\EngineInterface;

/**
 * Dashboard widget abstract implementation
 */
abstract class AbstractDashboardWidget implements DashboardWidgetInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    protected $adminRouter;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter         $adminRouter Admin router
     * @param \Symfony\Component\Templating\EngineInterface $templating  Templating
     */
    public function __construct(AdminRouter $adminRouter, EngineInterface $templating)
    {
        $this->adminRouter = $adminRouter;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermissions()
    {
        return array();
    }
}
