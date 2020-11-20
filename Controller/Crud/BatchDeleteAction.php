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
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CRUD controller batch delete action
 */
class BatchDeleteAction extends AbstractAction
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $this->getParentEntityDefinition($request);

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName()),
            CrudControllerEvents::STARTED
        );

        $form = $this->adminFormFactory->createBatchDeleteForm($this->getEntityClass())->handleRequest($request);

        $entities = $this->em->getRepository($this->getEntityClass())->findBy([
            $this->getMeta()->getIdentifier() => array_unique($form->get('ids')->getData()),
        ]);

        if (empty($entities)) {
            throw new \RuntimeException(
                sprintf('Unable to handle batch delete form for entity class "%s": entity array is empty.', $this->getEntityClass())
            );
        }
        foreach ($entities as $entity) {
            $this->checkPermission(Permission::CREATE_DELETE, $entity);
        }

        $redirectUrl = '/';

        if ($this->adminRouter->exists($this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            $redirectUrl = $this->adminRouter->generate(reset($entities), $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $referer = $request->headers->get('referer', $redirectUrl);

        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse(null, false, $message, [], $referer);
            }

            $this->flashNotifier->error($message);

            return new RedirectResponse($referer);
        }
        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();

        $this->clearCache();

        $user = $this->userManager->getCurrentUser();

        foreach ($entities as $entity) {
            $this->eventDispatcher->dispatch(new DeletedEvent($this->getMeta(), $user, $form, $entity), CrudEvents::DELETED);
        }

        $message = 'crud.action.batch_delete.success';

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
