<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Cache;

use Darvin\AdminBundle\Cache\CacheCleanerInterface;
use Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache Widget Clear controller
 */
class WidgetClearController
{
    /**
     * @var \Darvin\AdminBundle\Cache\CacheCleanerInterface
     */
    private $cacheCleaner;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface
     */
    private $cacheFormFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface
     */
    private $cacheFormRenderer;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $flashNotifier;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * ClearController constructor.
     * @param \Darvin\AdminBundle\Cache\CacheCleanerInterface                    $cacheCleaner      Cache cleaner
     * @param \Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface   $cacheFormFactory  Cache form factory
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface $cacheFormRenderer Cache from Render
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                         $flashNotifier     Flash notifier
     * @param \Symfony\Component\Routing\RouterInterface                         $router            Router
     */
    public function __construct(
        CacheCleanerInterface $cacheCleaner,
        CacheFormFactoryInterface $cacheFormFactory,
        CacheFormRendererInterface $cacheFormRenderer,
        FlashNotifierInterface $flashNotifier,
        RouterInterface $router
    ) {
        $this->cacheCleaner = $cacheCleaner;
        $this->cacheFormFactory = $cacheFormFactory;
        $this->cacheFormRenderer = $cacheFormRenderer;
        $this->flashNotifier = $flashNotifier;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->cacheFormFactory->createWidgetClearForm()->handleRequest($request);

        if (!$form->isValid()) {
            return $this->renderResponse($request, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
        }

        if ($this->cacheCleaner->run('fast') > 0) {
            return $this->renderResponse($request, false, 'cache.action.clear.error');
        }

        return $this->renderResponse($request, true, 'cache.action.clear.success');
    }

    /**
     * @param Request $request
     * @param bool $success
     * @param string $message
     *
     * @return Response
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function renderResponse(Request $request, bool $success, string $message): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($this->cacheFormRenderer->renderWidgetClearForm(), $success, $message);
        }

        if ($success) {
            $this->flashNotifier->success($message);
        } else {
            $this->flashNotifier->error($message);
        }

        return new RedirectResponse($request->headers->get('referer', $this->router->generate('darvin_admin_homepage')));
    }
}
