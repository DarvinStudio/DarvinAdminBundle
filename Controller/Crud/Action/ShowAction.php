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
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Show\EntityToShowViewTransformer;
use Darvin\Utils\CustomObject\CustomObjectException;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller show action
 */
class ShowAction extends AbstractAction
{
    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Darvin\AdminBundle\View\Show\EntityToShowViewTransformer
     */
    private $entityToShowViewTransformer;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface    $customObjectLoader          Custom object loader
     * @param \Darvin\AdminBundle\View\Show\EntityToShowViewTransformer $entityToShowViewTransformer Entity to show view transformer
     */
    public function __construct(CustomObjectLoaderInterface $customObjectLoader, EntityToShowViewTransformer $entityToShowViewTransformer)
    {
        $this->customObjectLoader = $customObjectLoader;
        $this->entityToShowViewTransformer = $entityToShowViewTransformer;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run(Request $request, $id): Response
    {
        $this->checkPermission(Permission::VIEW);

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($id);

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        try {
            $this->customObjectLoader->loadCustomObjects($entity);
        } catch (CustomObjectException $ex) {
        }

        $view = $this->entityToShowViewTransformer->transform($this->meta, $entity);

        return new Response(
            $this->renderTemplate('show', [
                'entity'        => $entity,
                'meta'          => $this->meta,
                'parent_entity' => $parentEntity,
                'view'          => $view,
            ], $request->isXmlHttpRequest())
        );
    }
}
