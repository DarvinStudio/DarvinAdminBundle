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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * List cache clear controller
 */
class ListController
{
    /**
     * @var \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $flashNotifier;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface
     */
    private $formRenderer;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface                   $cacheClearer  Cache clearer
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                              $flashNotifier Flash notifier
     * @param \Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface   $formFactory   List cache clear form factory
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\Clear\ListFormRendererInterface $formRenderer  List cache clear form renderer
     * @param \Symfony\Component\Routing\RouterInterface                              $router        Router
     * @param \Twig\Environment                                                       $twig          Twig
     */
    public function __construct(
        CacheClearerInterface $cacheClearer,
        FlashNotifierInterface $flashNotifier,
        ListFormFactoryInterface $formFactory,
        ListFormRendererInterface $formRenderer,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->cacheClearer = $cacheClearer;
        $this->flashNotifier = $flashNotifier;
        $this->formFactory = $formFactory;
        $this->formRenderer = $formRenderer;
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
        $form = $this->formFactory->createForm()->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->createResponse($request, $form);
        }
        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error): string {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            return $this->createResponse($request, $form, false, $message);
        }
        if ($this->cacheClearer->runCommands('list', $form->get('commands')->getData()) > 0) {
            return $this->createResponse($request, $form, false, 'list_cache.action.clear.error');
        }

        return $this->createResponse($request, $form, true, 'list_cache.action.clear.success');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param \Symfony\Component\Form\FormInterface     $form    Form
     * @param bool                                      $success Success
     * @param string|null                               $message Message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse(Request $request, FormInterface $form, bool $success = false, ?string $message = null): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($this->formRenderer->renderForm($success ? null : $form), $success, $message);
        }
        if (null !== $message) {
            $this->flashNotifier->done($success, $message);
        }
        if ($success) {
            return new RedirectResponse($this->router->generate('darvin_admin_cache_clear_list'));
        }

        return new Response($this->twig->render('@DarvinAdmin/cache/clear/list/form.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
