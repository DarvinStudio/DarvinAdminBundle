<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Cache;

use Darvin\AdminBundle\Cache\CacheCleanerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache form factory
 */
class WidgetFormFactory implements WidgetFormFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Cache\CacheCleanerInterface
     */
    private $cacheCleaner;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Darvin\AdminBundle\Cache\CacheCleanerInterface $cacheCleaner       Cache cleaner
     * @param \Symfony\Component\Form\FormFactoryInterface    $genericFormFactory Generic form factory
     * @param \Symfony\Component\Routing\RouterInterface      $router             Router
     */
    public function __construct(CacheCleanerInterface $cacheCleaner, FormFactoryInterface $genericFormFactory, RouterInterface $router)
    {
        $this->cacheCleaner = $cacheCleaner;
        $this->genericFormFactory = $genericFormFactory;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function createClearForm(array $options = []): FormInterface
    {
        return $this->genericFormFactory->createNamed('darvin_admin_cache_widget_clear', FormType::class, null, array_merge([
            'action'        => $this->router->generate('darvin_admin_cache_widget_clear'),
            'csrf_token_id' => md5(__FILE__.__METHOD__.'darvin_admin_cache_widget_clear'),
        ], $options));
    }
}
