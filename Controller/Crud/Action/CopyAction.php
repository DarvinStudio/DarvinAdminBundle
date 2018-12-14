<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud\Action;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CopiedEvent;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Form\FormHandler;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller copy action
 */
class CopyAction extends AbstractAction
{
    /**
     * @var \Darvin\AdminBundle\Form\FormHandler
     */
    private $formHandler;

    /**
     * @param \Darvin\AdminBundle\Form\FormHandler $formHandler Form handler
     */
    public function __construct(FormHandler $formHandler)
    {
        $this->formHandler = $formHandler;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run(Request $request, $id): Response
    {
        $this->checkPermission(Permission::CREATE_DELETE);

        $entity = $this->findEntity($id);

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        $form = $this->adminFormFactory->createCopyForm($entity, $this->entityClass)->handleRequest($request);

        $copy = $this->formHandler->handleCopyForm($form, $entity);

        if (!empty($copy)) {
            $this->eventDispatcher->dispatch(CrudEvents::COPIED, new CopiedEvent($this->meta, $this->userManager->getCurrentUser(), $entity, $copy));
        }

        $url = $request->headers->get(
            'referer',
            $this->adminRouter->generate($entity, $this->entityClass, AdminRouterInterface::TYPE_INDEX)
        );

        return new RedirectResponse($url);
    }
}
