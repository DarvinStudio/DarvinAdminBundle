<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Cache Twig extension
 */
class CacheExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface|null
     */
    private $cacheFormRender;

    /**
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface|null $cacheFormRender Cache clear form render
     */
    public function setWidgetFormRenderer(?WidgetFormRendererInterface $cacheFormRender): void
    {
        $this->cacheFormRender = $cacheFormRender;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_cache_clear_widget', [$this, 'renderCacheClearWidget'], [
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * @return string|null
     */
    public function renderCacheClearWidget(): ?string
    {
        if (null === $this->cacheFormRender) {
            return null;
        }

        return $this->cacheFormRender->renderClearForm();
    }
}
