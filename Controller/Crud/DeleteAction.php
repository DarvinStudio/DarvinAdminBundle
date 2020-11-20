<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\DeletedEvent;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Darvin\AdminBundle\View\Widget\Widget\DeleteFormWidget;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CRUD controller delete action
 */
class DeleteAction extends AbstractAction
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private $viewWidgetPool;

    /**
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface $viewWidgetPool View widget pool
     */
    public function __construct(ViewWidgetPoolInterface $viewWidgetPool)
    {
        $this->viewWidgetPool = $viewWidgetPool;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($request->attributes->get('id'));

        $this->checkPermission(Permission::CREATE_DELETE, $entity);

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName(), $entity),
            CrudControllerEvents::STARTED
        );

        $form        = $this->adminFormFactory->createDeleteForm($entity, $this->getEntityClass())->handleRequest($request);
        $redirectUrl = '/';

        if ($this->adminRouter->exists($this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            $redirectUrl = $this->adminRouter->generate($entity, $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $referer = $request->headers->get('referer', $redirectUrl);

        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($this->viewWidgetPool->getWidget(DeleteFormWidget::ALIAS)->getContent($entity), false, $message);
            }

            $this->flashNotifier->error($message);

            return new RedirectResponse($referer);
        }

        $this->em->remove($entity);
        $this->em->flush();

        $this->clearCache();

        $this->eventDispatcher->dispatch(new DeletedEvent($this->getMeta(), $this->userManager->getCurrentUser(), $form, $entity), CrudEvents::DELETED);

        $message = sprintf('%saction.delete.success', $this->getMeta()->getBaseTranslationPrefix());

        if (parse_url($referer, PHP_URL_PATH) === parse_url($redirectUrl, PHP_URL_PATH)) {
            $redirectUrl = $referer;
        }
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse(null, true, $message, [], $redirectUrl);
        }

        $this->flashNotifier->success($message);

        return new RedirectResponse($redirectUrl);
    }
}
