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
    private $widgetsFiltered;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->widgets = array();
        $this->widgetsFiltered = false;
    }

    /**
     * {@inheritdoc}
     */
    public function addWidget(DashboardWidgetInterface $widget)
    {
        $widgetName = $widget->getName();

        if (isset($this->widgets[$widgetName])) {
            throw new DashboardException(sprintf('Dashboard widget "%s" already added.', $widgetName));
        }

        $this->widgets[$widgetName] = $widget;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets()
    {
        $this->filterWidgets();

        return $this->widgets;
    }

    private function filterWidgets()
    {
        if ($this->widgetsFiltered) {
            return;
        }
        foreach ($this->widgets as $name => $widget) {
            $requiredPermissions = $widget->getRequiredPermissions();

            if (empty($requiredPermissions)) {
                continue;
            }
            foreach ($requiredPermissions as $objectClass => $permissions) {
                if (!$this->authorizationChecker->isGranted($permissions, $objectClass)) {
                    unset($this->widgets[$name]);
                }
            }
        }

        $this->widgetsFiltered = true;
    }
}
