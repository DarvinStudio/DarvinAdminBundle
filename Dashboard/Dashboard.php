<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Dashboard;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Dashboard
 */
class Dashboard implements DashboardInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Dashboard\DashboardWidgetInterface[]
     */
    private $widgets;

    /**
     * @var bool
     */
    private $filtered;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

        $this->widgets = [];

        $this->filtered = false;
    }

    /**
     * @param \Darvin\AdminBundle\Dashboard\DashboardWidgetInterface $widget Widget
     */
    public function addWidget(DashboardWidgetInterface $widget): void
    {
        $this->widgets[$widget->getName()] = $widget;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets(): iterable
    {
        if (!$this->filtered) {
            foreach ($this->widgets as $key => $widget) {
                foreach ($widget->getRequiredPermissions() as $class => $permissions) {
                    if (!is_array($permissions)) {
                        $permissions = [$permissions];
                    }
                    foreach ($permissions as $permission) {
                        if (!$this->authorizationChecker->isGranted($permission, $class)) {
                            unset($this->widgets[$key]);

                            continue 3;
                        }
                    }
                }
            }

            $this->filtered = true;
        }

        return $this->widgets;
    }
}
