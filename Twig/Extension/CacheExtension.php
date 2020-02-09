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

use Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Cache twig extension
 */
class CacheExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface
     */
    private $cacheFormRender;

    /**
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface $cacheFormRender Cache Clear form render
     */
    public function __construct(CacheFormRendererInterface $cacheFormRender)
    {
        $this->cacheFormRender = $cacheFormRender;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('admin_cache_clear_widget', [$this->cacheFormRender, 'renderWidgetClearForm'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }
}
