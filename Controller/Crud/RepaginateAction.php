<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface;
use Darvin\AdminBundle\Pagination\PaginationManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CRUD controller repaginate action
 */
class RepaginateAction extends AbstractAction
{
    /**
     * @var \Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface
     */
    private $paginationFormFactory;

    /**
     * @var \Darvin\AdminBundle\Pagination\PaginationManagerInterface
     */
    private $paginationManager;

    /**
     * @param \Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface $paginationFormFactory Pagination form factory
     * @param \Darvin\AdminBundle\Pagination\PaginationManagerInterface       $paginationManager     Pagination manager
     */
    public function __construct(PaginationFormFactoryInterface $paginationFormFactory, PaginationManagerInterface $paginationManager)
    {
        $this->paginationFormFactory = $paginationFormFactory;
        $this->paginationManager = $paginationManager;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        if (!$this->getConfig()['pagination']['enabled']) {
            throw new NotFoundHttpException(sprintf('Pagination is disabled for entity class "%s".', $this->getEntityClass()));
        }

        $this->checkPermission(Permission::VIEW);

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName()),
            CrudControllerEvents::STARTED
        );

        $request = $this->requestStack->getCurrentRequest();

        $form        = $this->paginationFormFactory->createRepaginateForm($this->getEntityClass())->handleRequest($request);
        $redirectUrl = '/';

        if ($this->adminRouter->exists($this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            $redirectUrl = $this->adminRouter->generate(null, $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $referer = $request->headers->get('referer', $redirectUrl);

        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            $this->flashNotifier->error($message);

            return new RedirectResponse($referer);
        }

        $this->paginationManager->setItemsPerPage($this->getEntityClass(), $form->get('itemsPerPage')->getData());

        return new RedirectResponse($referer);
    }
}
