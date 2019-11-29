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

use Darvin\AdminBundle\Search\SearcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Search index controller
 */
class IndexController
{
    /**
     * @var \Darvin\AdminBundle\Search\SearcherInterface
     */
    private $searcher;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var int
     */
    private $queryMinLength;

    /**
     * @param \Darvin\AdminBundle\Search\SearcherInterface $searcher       Searcher
     * @param \Twig\Environment                            $twig           Twig
     * @param mixed                                        $queryMinLength Search query min length
     */
    public function __construct(SearcherInterface $searcher, Environment $twig, $queryMinLength)
    {
        $this->searcher = $searcher;
        $this->twig = $twig;
        $this->queryMinLength = (int)$queryMinLength;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $query = htmlspecialchars($request->query->get('q', ''));

        $queryTooShort = mb_strlen($query) < $this->queryMinLength;

        $entityNames = $queryTooShort ? [] : $this->searcher->getSearchableEntityNames();

        return new Response($this->twig->render('@DarvinAdmin/search/index.html.twig', [
            'entity_names'     => $entityNames,
            'query'            => $query,
            'query_min_length' => $this->queryMinLength,
            'query_too_short'  => $queryTooShort,
        ]));
    }
}
