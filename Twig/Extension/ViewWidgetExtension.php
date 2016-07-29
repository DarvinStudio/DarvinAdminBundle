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

use Darvin\AdminBundle\View\Widget\WidgetPoolProvider;

/**
 * View widget Twig extension
 */
class ViewWidgetExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetPoolProvider
     */
    private $widgetPoolProvider;

    /**
     * @param \Darvin\AdminBundle\View\Widget\WidgetPoolProvider $widgetPoolProvider View widget pool provider
     */
    public function __construct(WidgetPoolProvider $widgetPoolProvider)
    {
        $this->widgetPoolProvider = $widgetPoolProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = [];

        foreach ($this->widgetPoolProvider->getWidgetPool()->getWidgets() as $alias => $widget) {
            $functions[] = new \Twig_SimpleFunction('admin_widget_'.$alias, [$widget, 'getContent'], [
                'is_safe' => ['html'],
            ]);
        }

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_view_widget_extension';
    }
}
