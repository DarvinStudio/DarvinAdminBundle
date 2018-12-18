<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Search\SearcherInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Factory\Index\IndexViewFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search controller
 */
class SearchController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request): Response
    {
        $query = htmlspecialchars($request->query->get('q'));

        $queryMinLength = $this->getParameter('darvin_admin.search_query_min_length');

        $queryTooShort = mb_strlen($query) < $queryMinLength;

        $entityNames = $queryTooShort ? [] : $this->getSearcher()->getSearchableEntityNames();

        return $this->render('@DarvinAdmin/search/index.html.twig', [
            'entity_names'     => $entityNames,
            'query'            => $query,
            'query_min_length' => $queryMinLength,
            'query_too_short'  => $queryTooShort,
        ]);
    }

    /**
     * @param string $entityName Entity name
     * @param string $query      Search query
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function resultsAction(string $entityName, string $query): Response
    {
        $searcher = $this->getSearcher();

        if (!$searcher->isSearchable($entityName)) {
            throw $this->createNotFoundException(sprintf('Entity "%s" is not searchable.', $entityName));
        }

        $entities = $this->getSearcher()->search($entityName, $query);

        $meta = $searcher->getSearchableEntityMeta($entityName);

        $batchDeleteForm = null;

        if (!empty($entities)
            && $this->isGranted(Permission::CREATE_DELETE, $meta->getEntityClass())
            && $this->getAdminRouter()->exists($meta->getEntityClass(), AdminRouterInterface::TYPE_BATCH_DELETE)
        ) {
            $batchDeleteForm = $this->getAdminFormFactory()->createBatchDeleteForm($meta->getEntityClass(), $entities)->createView();
        }

        $view = $this->getIndexViewFactory()->createView($entities, $meta);

        return $this->render('@DarvinAdmin/search/results.html.twig', [
            'batch_delete_form' => $batchDeleteForm,
            'entity_count'      => count($entities),
            'meta'              => $meta,
            'view'              => $view,
        ]);
    }

    /**
     * @return \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private function getAdminFormFactory(): AdminFormFactory
    {
        return $this->get('darvin_admin.form.factory');
    }

    /**
     * @return \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private function getAdminRouter(): AdminRouterInterface
    {
        return $this->get('darvin_admin.router');
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Index\IndexViewFactoryInterface
     */
    private function getIndexViewFactory(): IndexViewFactoryInterface
    {
        return $this->get('darvin_admin.view.factory.index');
    }

    /**
     * @return \Darvin\AdminBundle\Search\SearcherInterface
     */
    private function getSearcher(): SearcherInterface
    {
        return $this->get('darvin_admin.searcher');
    }
}
