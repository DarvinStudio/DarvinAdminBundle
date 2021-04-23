<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension\Push;

use Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Push widget Twig extension
 */
class WidgetExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface
     */
    private $pushProviderRegistry;

    /**
     * @param \Darvin\AdminBundle\Push\Provider\Registry\PushProviderRegistryInterface $pushProviderRegistry Push provider registry
     */
    public function __construct(PushProviderRegistryInterface $pushProviderRegistry)
    {
        $this->pushProviderRegistry = $pushProviderRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('darvin_admin_push_widget', [$this, 'render'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param \Twig\Environment $twig Twig
     *
     * @return string
     */
    public function render(Environment $twig): string
    {
        if (!$this->pushProviderRegistry->isEmpty()) {
            return $twig->render('@DarvinAdmin/push/widget.html.twig');
        }

        return '';
    }
}
