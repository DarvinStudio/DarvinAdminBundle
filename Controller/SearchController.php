<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
    public function indexAction(Request $request)
    {
        $query = htmlspecialchars($request->query->get('q'));

        $queryMinLength = $this->getParameter('darvin_admin.search_query_min_length');

        $queryTooShort = mb_strlen($query) < $queryMinLength;

        $entityNames = $queryTooShort ? [] : $this->getSearcher()->getSearchableEntityNames();

        return $this->render('DarvinAdminBundle:Search:index.html.twig', [
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
    public function resultsAction($entityName, $query)
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
            && $this->getAdminRouter()->isRouteExists($meta->getEntityClass(), AdminRouter::TYPE_BATCH_DELETE)
        ) {
            $batchDeleteForm = $this->getAdminFormFactory()->createBatchDeleteForm($meta->getEntityClass(), $entities)->createView();
        }

        $view = $this->getEntitiesToIndexViewTransformer()->transform($meta, $entities);

        return $this->render('DarvinAdminBundle:Search/widget:results.html.twig', [
            'batch_delete_form' => $batchDeleteForm,
            'entities_count'    => count($entities),
            'meta'              => $meta,
            'view'              => $view,
        ]);
    }

    /**
     * @return \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private function getAdminFormFactory()
    {
        return $this->get('darvin_admin.form.factory');
    }

    /**
     * @return \Darvin\AdminBundle\Route\AdminRouter
     */
    private function getAdminRouter()
    {
        return $this->get('darvin_admin.router');
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer
     */
    private function getEntitiesToIndexViewTransformer()
    {
        return $this->get('darvin_admin.view.entity_transformer.index');
    }

    /**
     * @return \Darvin\AdminBundle\Search\Searcher
     */
    private function getSearcher()
    {
        return $this->get('darvin_admin.searcher');
    }
}
