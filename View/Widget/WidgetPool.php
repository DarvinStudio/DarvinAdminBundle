<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget;

/**
 * View widget pool
 */
class WidgetPool
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetInterface[]
     */
    private $widgets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->widgets = [];
    }

    /**
     * @param \Darvin\AdminBundle\View\Widget\WidgetInterface $widget View widget
     *
     * @throws \Darvin\AdminBundle\View\Widget\WidgetException
     */
    public function addWidget(WidgetInterface $widget)
    {
        $alias = $widget->getAlias();

        if (isset($this->widgets[$alias])) {
            throw new WidgetException(sprintf('View widget with alias "%s" already added to pool.', $alias));
        }

        $this->widgets[$alias] = $widget;
    }

    /**
     * @return array
     */
    public function getWidgetAliases()
    {
        return array_keys($this->widgets);
    }

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetInterface[]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param string $alias View widget alias
     *
     * @return \Darvin\AdminBundle\View\Widget\WidgetInterface
     * @throws \Darvin\AdminBundle\View\Widget\WidgetException
     */
    public function getWidget($alias)
    {
        if (!isset($this->widgets[$alias])) {
            throw new WidgetException(sprintf('View widget with alias "%s" does not exist.', $alias));
        }

        return $this->widgets[$alias];
    }
}
