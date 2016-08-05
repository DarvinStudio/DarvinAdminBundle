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

use Darvin\Utils\Service\ServiceProviderInterface;

/**
 * View widget Twig extension
 */
class ViewWidgetExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $widgetPoolProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $widgetPoolProvider View widget pool provider
     */
    public function __construct(ServiceProviderInterface $widgetPoolProvider)
    {
        $this->widgetPoolProvider = $widgetPoolProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = [];

        foreach ($this->getWidgetPool()->getWidgets() as $alias => $widget) {
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

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    private function getWidgetPool()
    {
        return $this->widgetPoolProvider->getService();
    }
}
