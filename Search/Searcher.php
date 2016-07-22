<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Search;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\ContentBundle\Filterer\FiltererInterface;
use Doctrine\ORM\EntityManager;

/**
 * Searcher
 */
class Searcher
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\ContentBundle\Filterer\FiltererInterface
     */
    private $filterer;

    /**
     * @param \Doctrine\ORM\EntityManager                      $em       Entity manager
     * @param \Darvin\ContentBundle\Filterer\FiltererInterface $filterer Filterer
     */
    public function __construct(EntityManager $em, FiltererInterface $filterer)
    {
        $this->em = $em;
        $this->filterer = $filterer;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta  Metadata
     * @param string                                $query Search query
     *
     * @return object[]
     * @throws \Darvin\AdminBundle\Search\SearchException
     */
    public function search(Metadata $meta, $query)
    {
        if (!$this->isSearchable($meta)) {
            throw new SearchException(sprintf('Entity "%s" is not searchable.', $meta->getEntityClass()));
        }

        $qb = $this->em->getRepository($meta->getEntityClass())->createQueryBuilder('o');

        $searchableFields = $this->getSearchableFields($meta);

        $this->filterer->filter($qb, array_fill_keys($searchableFields, $query), [
            'non_strict_comparison_fields' => $searchableFields,
        ], false);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return bool
     */
    public function isSearchable(Metadata $meta)
    {
        $searchableFields = $this->getSearchableFields($meta);

        return !empty($searchableFields);
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return string[]
     */
    private function getSearchableFields(Metadata $meta)
    {
        $config = $meta->getConfiguration();

        return $config['searchable_fields'];
    }
}
