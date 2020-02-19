<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension\Cache;

use Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Cache clear Twig extension
 */
class ClearExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface|null
     */
    private $formRenderer;

    /**
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface|null $formRenderer Widget cache clear form renderer
     */
    public function __construct(?WidgetFormRendererInterface $formRenderer)
    {
        $this->formRenderer = $formRenderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_cache_clear_form', [$this, 'renderForm'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return string|null
     */
    public function renderForm(): ?string
    {
        if (null === $this->formRenderer) {
            return null;
        }

        return $this->formRenderer->renderForm();
    }
}
