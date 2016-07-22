<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

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
        $query = $request->query->get('q');

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

        $view = $this->getEntitiesToIndexViewTransformer()->transform(
            $searcher->getSearchableEntityMeta($entityName),
            $entities
        );

        return $this->render('DarvinAdminBundle:Search/widget:results.html.twig', [
            'entities_count' => count($entities),
            'view'           => $view,
        ]);
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
