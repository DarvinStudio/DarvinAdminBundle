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
use Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\Cache\Clear\ListFormRendererInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Cache clear list controller
 */
class ListController
{
    /**
     * @var \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface
     */
    private $cacheFormFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface
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
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface                   $cacheClearer      Cache clearer
     * @param \Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface   $cacheFormFactory  Cache form factory
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\Clear\ListFormRendererInterface $cacheFormRenderer Cache from Render
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                              $flashNotifier     Flash notifier
     * @param \Symfony\Component\Routing\RouterInterface                              $router            Router
     * @param \Twig\Environment                                                       $twig              Twig
     */
    public function __construct(
        CacheClearerInterface $cacheClearer,
        ListFormFactoryInterface $cacheFormFactory,
        ListFormRendererInterface $cacheFormRenderer,
        FlashNotifierInterface $flashNotifier,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->cacheClearer = $cacheClearer;
        $this->cacheFormFactory = $cacheFormFactory;
        $this->cacheFormRenderer = $cacheFormRenderer;
        $this->flashNotifier = $flashNotifier;
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->cacheFormFactory->createClearForm()->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->createResponse($request, $form);
        }
        if (!$form->isValid()) {
            return $this->createResponse($request, $form, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
        }
        if ($this->cacheClearer->runCommands('list', $form->get('ids')->getData()) > 0) {
            return $this->createResponse($request, $form, false, 'cache.action.clear.error');
        }

        return $this->createResponse($request, $form, true, 'cache.action.clear.success');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param \Symfony\Component\Form\FormInterface     $form    Form
     * @param bool                                      $success Success
     * @param string|null                               $message Message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse(Request $request, FormInterface $form, bool $success = true, ?string $message = null): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($this->cacheFormRenderer->renderForm(), $success, $message);
        }
        if (null !== $message) {
            $this->flashNotifier->done($success, $message);
        }

        return new Response($this->twig->render('@DarvinAdmin/cache/clear/list.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
