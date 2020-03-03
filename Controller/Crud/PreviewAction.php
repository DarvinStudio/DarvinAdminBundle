<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ContentBundle\Controller\ContentControllerPoolInterface;
use Darvin\ContentBundle\Controller\ControllerNotExistsException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CRUD controller preview action
 */
class PreviewAction extends AbstractAction
{
    /**
     * @var \Darvin\ContentBundle\Controller\ContentControllerPoolInterface
     */
    private $contentControllerPool;

    /**
     * @param \Darvin\ContentBundle\Controller\ContentControllerPoolInterface $contentControllerPool Content controller pool
     */
    public function __construct(ContentControllerPoolInterface $contentControllerPool)
    {
        $this->contentControllerPool = $contentControllerPool;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $entity = $this->findEntity($request->attributes->get('id'));

        $this->checkPermission(Permission::VIEW, $entity);

        try {
            $contentController = $this->contentControllerPool->getController($this->getEntityClass());
        } catch (ControllerNotExistsException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName(), $entity),
            CrudControllerEvents::STARTED
        );

        return $contentController->__invoke($request, $entity);
    }
}
