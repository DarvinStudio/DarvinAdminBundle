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
use Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
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
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface                     $cacheClearer  Cache clearer
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                                $flashNotifier Flash notifier
     * @param \Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface   $formFactory   Widget cache clear form factory
     * @param \Darvin\AdminBundle\Form\Renderer\Cache\Clear\WidgetFormRendererInterface $formRenderer  Widget cache clear form renderer
     * @param \Symfony\Component\Routing\RouterInterface                                $router        Router
     */
    public function __construct(
        CacheClearerInterface $cacheClearer,
        FlashNotifierInterface $flashNotifier,
        WidgetFormFactoryInterface $formFactory,
        WidgetFormRendererInterface $formRenderer,
        RouterInterface $router
    ) {
        $this->cacheClearer = $cacheClearer;
        $this->flashNotifier = $flashNotifier;
        $this->formFactory = $formFactory;
        $this->formRenderer = $formRenderer;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->createForm()->handleRequest($request);

        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error): string {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            return $this->createResponse($request, $form, false, $message);
        }
        if ($this->cacheClearer->runCommands('widget') > 0) {
            return $this->createResponse($request, $form, false, 'cache.clear.widget.done.error');
        }

        return $this->createResponse($request, $form, true, 'cache.clear.widget.done.success');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param \Symfony\Component\Form\FormInterface     $form    Form
     * @param bool                                      $success Success
     * @param string                                    $message Message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse(Request $request, FormInterface $form, bool $success, string $message): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($this->formRenderer->renderForm($success ? null : $form), $success, $message);
        }

        $this->flashNotifier->done($success, $message);

        return new RedirectResponse($request->headers->get('referer', $this->router->generate('darvin_admin_homepage')));
    }
}
