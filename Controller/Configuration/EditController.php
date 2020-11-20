<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Configuration;

use Darvin\AdminBundle\Cache\Clear\CacheClearerInterface;
use Darvin\AdminBundle\Form\Type\Configuration\ConfigurationsType;
use Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Configuration edit controller
 *
 * @Security("is_granted('admin_edit', 'Darvin\ConfigBundle\Entity\ParameterEntity')")
 */
class EditController
{
    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface
     */
    private $configPool;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $flashNotifier;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
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
     * @var \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface|null
     */
    private $cacheClearer;

    /**
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface $configPool    Configuration pool
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                    $flashNotifier Flash notifier
     * @param \Symfony\Component\Form\FormFactoryInterface                  $formFactory   Form factory
     * @param \Symfony\Component\Routing\RouterInterface                    $router        Router
     * @param \Twig\Environment                                             $twig          Twig
     */
    public function __construct(
        ConfigurationPoolInterface $configPool,
        FlashNotifierInterface $flashNotifier,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->configPool = $configPool;
        $this->flashNotifier = $flashNotifier;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->twig = $twig;
    }

    /**
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface|null $cacheClearer Cache clearer
     */
    public function setCacheClearer(?CacheClearerInterface $cacheClearer): void
    {
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->createEditForm()->handleRequest($request);
        $twig = $this->twig;

        $render = function (FormInterface $form) use ($request, $twig) {
            return $twig->render(sprintf('@DarvinAdmin/configuration/%sedit.html.twig', $request->isXmlHttpRequest() ? '_' : ''), [
                'form' => $form->createView(),
            ]);
        };

        if (!$form->isSubmitted()) {
            return new Response($render($form));
        }
        if (!$form->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($render($form), false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
            }

            $this->flashNotifier->formError();

            return new Response($render($form));
        }

        $this->configPool->saveAll();

        $this->clearCache();

        $message = 'configuration.action.edit.success';

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($render($this->createEditForm()), true, $message);
        }

        $this->flashNotifier->success($message);

        return new RedirectResponse($this->router->generate('darvin_admin_configuration'));
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createEditForm(): FormInterface
    {
        return $this->formFactory->create(ConfigurationsType::class, $this->configPool, [
            'action'             => $this->router->generate('darvin_admin_configuration'),
            'translation_domain' => 'admin',
        ]);
    }

    private function clearCache(): void
    {
        if (null !== $this->cacheClearer) {
            $this->cacheClearer->clearOnCrud();
        }
    }
}
