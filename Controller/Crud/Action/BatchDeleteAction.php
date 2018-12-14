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
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\DeletedEvent;
use Darvin\AdminBundle\Form\FormHandler;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller batch delete action
 */
class BatchDeleteAction extends AbstractAction
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     */
    public function run(Request $request): Response
    {
        $this->checkPermission(Permission::CREATE_DELETE);

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
        if ($this->formHandler->handleBatchDeleteForm($form, $entities)) {
            $user = $this->userManager->getCurrentUser();

            foreach ($entities as $entity) {
                $this->eventDispatcher->dispatch(CrudEvents::DELETED, new DeletedEvent($this->getMeta(), $user, $entity));
            }

            return new RedirectResponse($this->adminRouter->generate(reset($entities), $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX));
        }

        $url = $request->headers->get(
            'referer',
            $this->adminRouter->generate(reset($entities), $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)
        );

        return new RedirectResponse($url);
    }
}
