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
use Darvin\AdminBundle\Event\Crud\DeletedEvent;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Doctrine\Common\Collections\Collection;
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
        $this->checkPermission(Permission::CREATE_DELETE);

        $request = $this->requestStack->getCurrentRequest();

        $this->getParentEntityDefinition($request);

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), __FUNCTION__));

        $form = $this->adminFormFactory->createBatchDeleteForm($this->getEntityClass())->handleRequest($request);

        $entities = $form->get('entities')->getData();

        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        }
        if (empty($entities)) {
            throw new \RuntimeException(
                sprintf('Unable to handle batch delete form for entity class "%s": entity array is empty.', $this->getEntityClass())
            );
        }

        $redirectUrl = $this->adminRouter->generate(reset($entities), $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX, [], UrlGeneratorInterface::ABSOLUTE_URL);

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

        $user = $this->userManager->getCurrentUser();

        foreach ($entities as $entity) {
            $this->eventDispatcher->dispatch(CrudEvents::DELETED, new DeletedEvent($this->getMeta(), $user, $entity));
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
