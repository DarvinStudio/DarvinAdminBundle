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
     * @return \Darvin\AdminBundle\Search\Searcher
     */
    private function getSearcher()
    {
        return $this->get('darvin_admin.searcher');
    }
}
