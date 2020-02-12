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

use Darvin\AdminBundle\Cache\CacheCleanerInterface;
use Darvin\AdminBundle\Form\Factory\Cache\ListFormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Clear form renderer
 */
class ListFormRenderer implements ListFormRendererInterface
{
    /**
     * @var \Darvin\AdminBundle\Cache\CacheCleanerInterface
     */
    private $cacheCleaner;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\ListFormFactoryInterface
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
     * @param \Darvin\AdminBundle\Cache\CacheCleanerInterface                 $cacheCleaner Cache cleaner
     * @param \Darvin\AdminBundle\Form\Factory\Cache\ListFormFactoryInterface $formFactory  Clear Form factory
     * @param \Symfony\Component\Routing\RouterInterface                      $router       Router
     * @param \Twig\Environment                                               $twig         Twig
     */
    public function __construct(
        CacheCleanerInterface $cacheCleaner,
        ListFormFactoryInterface $formFactory,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->cacheCleaner = $cacheCleaner;
        $this->formFactory  = $formFactory;
        $this->router       = $router;
        $this->twig         = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderClearForm(): ?string
    {
        if (!$this->cacheCleaner->hasCommands('list')) {
            return null;
        }

        $form = $this->formFactory->createClearForm();

        return $this->twig->render('@DarvinAdmin/cache/clear/_list.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
