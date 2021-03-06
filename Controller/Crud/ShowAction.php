<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Factory\Show\ShowViewFactoryInterface;
use Darvin\Utils\CustomObject\CustomObjectException;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
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
     * @var \Darvin\AdminBundle\View\Factory\Show\ShowViewFactoryInterface
     */
    private $showViewFactory;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface         $customObjectLoader Custom object loader
     * @param \Darvin\AdminBundle\View\Factory\Show\ShowViewFactoryInterface $showViewFactory    Show view factory
     */
    public function __construct(CustomObjectLoaderInterface $customObjectLoader, ShowViewFactoryInterface $showViewFactory)
    {
        $this->customObjectLoader = $customObjectLoader;
        $this->showViewFactory = $showViewFactory;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($request->attributes->get('id'));

        $this->checkPermission(Permission::VIEW, $entity);

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName(), $entity),
            CrudControllerEvents::STARTED
        );

        try {
            $this->customObjectLoader->loadCustomObjects($entity);
        } catch (CustomObjectException $ex) {
        }

        $view = $this->showViewFactory->createView($entity, $this->getMeta());

        return new Response(
            $this->renderTemplate([
                'entity'        => $entity,
                'meta'          => $this->getMeta(),
                'parent_entity' => $parentEntity,
                'view'          => $view,
            ], $request->isXmlHttpRequest())
        );
    }
}
