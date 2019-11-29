<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Search;

use Darvin\AdminBundle\Form\AdminFormFactoryInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Search\SearcherInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Factory\Index\IndexViewFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Search results controller
 */
class ResultsController
{
    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactoryInterface
     */
    private $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\IndexViewFactoryInterface
     */
    private $indexViewFactory;

    /**
     * @var \Darvin\AdminBundle\Search\SearcherInterface
     */
    private $searcher;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactoryInterface                           $adminFormFactory     Admin form factory
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface                               $adminRouter          Admin router
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\View\Factory\Index\IndexViewFactoryInterface             $indexViewFactory     Index view factory
     * @param \Darvin\AdminBundle\Search\SearcherInterface                                 $searcher             Searcher
     * @param \Twig\Environment                                                            $twig                 Twig
     */
    public function __construct(
        AdminFormFactoryInterface $adminFormFactory,
        AdminRouterInterface $adminRouter,
        AuthorizationCheckerInterface $authorizationChecker,
        IndexViewFactoryInterface $indexViewFactory,
        SearcherInterface $searcher,
        Environment $twig
    ) {
        $this->adminFormFactory = $adminFormFactory;
        $this->adminRouter = $adminRouter;
        $this->authorizationChecker = $authorizationChecker;
        $this->indexViewFactory = $indexViewFactory;
        $this->searcher = $searcher;
        $this->twig = $twig;
    }

    /**
     * @param string $entityName Entity name
     * @param string $query      Search query
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(string $entityName, string $query): Response
    {
        if (!$this->searcher->isSearchable($entityName)) {
            throw new NotFoundHttpException(sprintf('Entity "%s" is not searchable.', $entityName));
        }

        $entities = $this->searcher->search($entityName, $query);

        $meta = $this->searcher->getSearchableEntityMeta($entityName);

        $batchDeleteForm = null;

        if (!empty($entities)
            && $this->adminRouter->exists($meta->getEntityClass(), AdminRouterInterface::TYPE_BATCH_DELETE)
        ) {
            $deletableEntities = [];

            foreach ($entities as $entity) {
                if ($this->authorizationChecker->isGranted(Permission::CREATE_DELETE, $entity)) {
                    $deletableEntities[] = $entity;
                }
            }
            if (!empty($deletableEntities)) {
                $batchDeleteForm = $this->adminFormFactory->createBatchDeleteForm($meta->getEntityClass(), $deletableEntities)->createView();
            }
        }

        $view = $this->indexViewFactory->createView($entities, $meta);

        return new Response($this->twig->render('@DarvinAdmin/search/results.html.twig', [
            'batch_delete_form' => $batchDeleteForm,
            'entity_count'      => count($entities),
            'meta'              => $meta,
            'view'              => $view,
        ]));
    }
}
