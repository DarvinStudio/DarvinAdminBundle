<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\View\Widget\WidgetPool;
use Darvin\Utils\Service\ServiceProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * View widget Twig extension
 */
class ViewWidgetExtension extends AbstractExtension
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
    public function getFunctions(): iterable
    {
        foreach ($this->getWidgetPool()->getWidgets() as $alias => $widget) {
            yield new TwigFunction(sprintf('admin_widget_%s', $alias), [$widget, 'getContent'], [
                'is_safe' => ['html'],
            ]);
        }
    }

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    private function getWidgetPool(): WidgetPool
    {
        return $this->widgetPoolProvider->getService();
    }
}
