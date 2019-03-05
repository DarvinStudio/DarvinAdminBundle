<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
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
        yield new TwigFunction('admin_widget', [$this, 'renderWidget'], [
            'is_safe' => ['html'],
        ]);
    }

    /**
     * @param string $alias   Widget alias
     * @param mixed  ...$args Widget arguments
     *
     * @return string|null
     */
    public function renderWidget(string $alias, ...$args): ?string
    {
        return $this->getWidgetPool()->getWidget($alias)->getContent(...$args);
    }

    /**
     * @return \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private function getWidgetPool(): ViewWidgetPoolInterface
    {
        return $this->widgetPoolProvider->getService();
    }
}
