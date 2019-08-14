<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\ClearableErrorsInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller edit action
 */
class EditAction extends AbstractAction
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        $this->checkPermission(Permission::EDIT);

        $request = $this->requestStack->getCurrentRequest();

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($request->attributes->get('id'));

        $entityBefore = clone $entity;

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        $form = $this->adminFormFactory->createEntityForm(
            $this->getMeta(),
            $entity,
            $this->getName(),
            $this->adminRouter->generate($entity, $this->getEntityClass(), AdminRouterInterface::TYPE_EDIT),
            $this->getSubmitButtons()
        )->handleRequest($request);

        if (!$form->isSubmitted() || $request->query->has('reload')) {
            if ($request->query->has('reload') && $form instanceof ClearableErrorsInterface) {
                $form->clearErrors(true);
            }

            return new Response($this->renderEditTemplate($entity, $form, $parentEntity, $request->isXmlHttpRequest()));
        }
        if (!$form->isValid()) {
            if (!$request->isXmlHttpRequest()) {
                $this->flashNotifier->formError();
            }

            $html = $this->renderEditTemplate($entity, $form, $parentEntity, $request->isXmlHttpRequest());

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($html, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
            }

            return new Response($html);
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch(CrudEvents::UPDATED, new UpdatedEvent($this->getMeta(), $this->userManager->getCurrentUser(), $entityBefore, $entity));

        $message     = sprintf('%saction.edit.success', $this->getMeta()->getBaseTranslationPrefix());
        $redirectUrl = $this->successRedirect($form, $entity);

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse(null, true, $message, [], $redirectUrl);
        }

        $this->flashNotifier->success($message);

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param object                                $entity       Entity
     * @param \Symfony\Component\Form\FormInterface $form         Form
     * @param object|null                           $parentEntity Parent entity
     * @param bool                                  $partial      Whether to render partial
     *
     * @return string
     */
    private function renderEditTemplate($entity, FormInterface $form, $parentEntity, bool $partial = false): string
    {
        return $this->renderTemplate([
            'entity'        => $entity,
            'form'          => $form->createView(),
            'meta'          => $this->getMeta(),
            'parent_entity' => $parentEntity,
        ], $partial);
    }
}
