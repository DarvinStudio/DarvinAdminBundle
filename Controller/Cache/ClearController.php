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

use Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Cache Clear controller
 */
class ClearController
{
    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $flashNotifier;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $kernel;

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\CacheFormFactoryInterface
     */
    private $cacheFormFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\Cache\CacheFormRendererInterface
     */
    private $cacheFormRenderer;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var \Darvin\AdminBundle\Command\ClearCachesCommand
     */
    private $clearCacheCommand;

    /**
     * ClearController constructor.
     * @param CacheFormFactoryInterface $cacheFormFactory
     * @param CacheFormRendererInterface $cacheFormRenderer
     * @param \Darvin\Utils\Flash\FlashNotifierInterface $flashNotifier Flash notifier
     * @param KernelInterface $kernel
     * @param RouterInterface $router
     * @param Environment $twig
     * @param Command $clearCacheCommand
     */
    public function __construct(
        CacheFormFactoryInterface $cacheFormFactory,
        CacheFormRendererInterface $cacheFormRenderer,
        FlashNotifierInterface $flashNotifier,
        KernelInterface $kernel,
        RouterInterface $router,
        Environment $twig,
        Command $clearCacheCommand
    ) {
        $this->cacheFormFactory = $cacheFormFactory;
        $this->cacheFormRenderer = $cacheFormRenderer;
        $this->flashNotifier = $flashNotifier;
        $this->kernel = $kernel;
        $this->router = $router;
        $this->twig = $twig;
        $this->clearCacheCommand = $clearCacheCommand;
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
            return new Response($this->twig->render('@DarvinAdmin/cache/clear.html.twig', [
                'form' => $form->createView(),
            ]));
        }

        if (!$form->isValid() || empty($form->getData()['ids'])) {
            return $this->renderResponse($request, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $this->clearCacheCommand->setApplication($application);
        $this->clearCacheCommand->addCacheIds($form->getData()['ids']);

        if ($this->clearCacheCommand->run(new ArrayInput([]), new NullOutput()) > 0) {
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
            return new AjaxResponse($this->cacheFormRenderer->renderClearForm(), $success, $message);
        }

        if ($success) {
            $this->flashNotifier->success($message);
        } else {
            $this->flashNotifier->error($message);
        }

        return new Response($this->twig->render('@DarvinAdmin/cache/clear.html.twig', [
            'form' => $this->cacheFormFactory->createClearForm()->createView(),
        ]));
    }
}
