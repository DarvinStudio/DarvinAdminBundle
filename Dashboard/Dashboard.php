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

/**
 * Dashboard
 */
class Dashboard implements DashboardInterface
{
    /**
     * @var \Darvin\AdminBundle\Dashboard\DashboardWidgetInterface[]
     */
    private $widgets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->widgets = array();
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
    public function getAllWidgets()
    {
        return $this->widgets;
    }
}
