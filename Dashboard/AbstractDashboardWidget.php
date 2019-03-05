<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dashboard;

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Templating\EngineInterface;

/**
 * Dashboard widget abstract implementation
 */
abstract class AbstractDashboardWidget implements DashboardWidgetInterface
{
    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    protected $adminRouter;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var string|null
     */
    private $name = null;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouterInterface $adminRouter): void
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating Templating
     */
    public function setTemplating(EngineInterface $templating): void
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermissions(): iterable
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        if (null === $this->name) {
            $this->name = StringsUtil::toUnderscore(preg_replace('/^.*\\\|Widget$/', '', get_class($this)));
        }

        return $this->name;
    }
}
