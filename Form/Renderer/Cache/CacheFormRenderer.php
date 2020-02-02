<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer\Cache;

use Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Clear form renderer
 */
class CacheFormRenderer implements CacheFormRendererInterface
{
    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\ClearFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $fastCaches;

    /**
     * @var array
     */
    private $listCaches;

    /**
     * @param \Darvin\AdminBundle\Form\Factory\Cache\ClearFormFactoryInterface $formFactory Form factory
     * @param \Symfony\Component\Routing\RouterInterface                       $router      Router
     * @param \Twig\Environment                                                $twig        Twig
     * @param array                                                            $fastCaches  Caches for fast clear
     * @param array                                                            $listCaches  Full caches list for clear
     */
    public function __construct(CacheFormFactoryInterface $formFactory, RouterInterface $router, Environment $twig, array $fastCaches, array $listCaches)
    {
        $this->formFactory = $formFactory;
        $this->router      = $router;
        $this->twig        = $twig;
        $this->fastCaches  = $fastCaches;
        $this->listCaches  = $listCaches;
    }

    /**
     * {@inheritDoc}
     */
    public function renderFastClearForm(): ?string
    {
        if (empty($this->fastCaches)) {
            return null;
        }

        $form = $this->formFactory->createFastClearForm();

        return $this->twig->render('@DarvinAdmin/cache/fast_clear.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderClearForm(): ?string
    {
        if (empty($this->listCaches)) {
            return null;
        }

        $form = $this->formFactory->createClearForm($this->listCaches);

        return $this->twig->render('@DarvinAdmin/cache/_clear.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
