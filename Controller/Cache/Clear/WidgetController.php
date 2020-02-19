<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Cache\Clear;

use Darvin\AdminBundle\Cache\Clear\CacheClearerInterface;
use Darvin\AdminBundle\Form\Factory\Cache\WidgetFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\Cache\WidgetFormRendererInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache clear widget controller
 */
class WidgetController
{
    /**
     * @var \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\WidgetFormFactoryInterface
     */
    private $cacheFormFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\WidgetFormRendererInterface
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
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface               $cacheClearer      Cache clearer
     * @param \Darvin\AdminBundle\Form\Factory\Cache\WidgetFormFactoryInterface   $cacheFormFactory  Cache form factory
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\WidgetFormRendererInterface $cacheFormRenderer Cache from Render
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                          $flashNotifier     Flash notifier
     * @param \Symfony\Component\Routing\RouterInterface                          $router            Router
     */
    public function __construct(
        CacheClearerInterface $cacheClearer,
        WidgetFormFactoryInterface $cacheFormFactory,
        WidgetFormRendererInterface $cacheFormRenderer,
        FlashNotifierInterface $flashNotifier,
        RouterInterface $router
    ) {
        $this->cacheClearer = $cacheClearer;
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
        $form = $this->cacheFormFactory->createClearForm()->handleRequest($request);

        if (!$form->isValid()) {
            return $this->createResponse($request, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
        }
        if ($this->cacheClearer->runCommands('widget') > 0) {
            return $this->createResponse($request, false, 'cache.action.clear.error');
        }

        return $this->createResponse($request, true, 'cache.action.clear.success');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param bool                                      $success Success
     * @param string                                    $message Message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse(Request $request, bool $success, string $message): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($this->cacheFormRenderer->renderClearForm(), $success, $message);
        }

        $this->flashNotifier->done($success, $message);

        return new RedirectResponse($request->headers->get('referer', $this->router->generate('darvin_admin_homepage')));
    }
}
