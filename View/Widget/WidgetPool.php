<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget;

/**
 * View widget pool
 */
class WidgetPool implements ViewWidgetPoolInterface
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
     * @throws \InvalidArgumentException
     */
    public function addWidget(WidgetInterface $widget): void
    {
        $alias = $widget->getAlias();

        if ($this->hasWidget($alias)) {
            throw new \InvalidArgumentException(sprintf('View widget "%s" already exists.', $alias));
        }

        $this->widgets[$alias] = $widget;
    }

    /**
     * {@inheritDoc}
     */
    public function getWidget(string $alias): WidgetInterface
    {
        if (!$this->hasWidget($alias)) {
            throw new \InvalidArgumentException(sprintf('View widget "%s" does not exist.', $alias));
        }

        return $this->widgets[$alias];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgetAliases(): array
    {
        return array_keys($this->widgets);
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * @param string $alias View widget alias
     *
     * @return bool
     */
    private function hasWidget(string $alias): bool
    {
        return isset($this->widgets[$alias]);
    }
}
