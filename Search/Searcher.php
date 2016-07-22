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
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\ContentBundle\Filterer\FiltererException;
use Darvin\ContentBundle\Filterer\FiltererInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\QueryException;

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
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private $searchableEntitiesMeta;

    /**
     * @param \Doctrine\ORM\EntityManager                      $em              Entity manager
     * @param \Darvin\ContentBundle\Filterer\FiltererInterface $filterer        Filterer
     * @param \Darvin\AdminBundle\Metadata\MetadataManager     $metadataManager Metadata manager
     */
    public function __construct(EntityManager $em, FiltererInterface $filterer, MetadataManager $metadataManager)
    {
        $this->em = $em;
        $this->filterer = $filterer;
        $this->metadataManager = $metadataManager;

        $this->searchableEntitiesMeta = null;
    }

    /**
     * @param string $entityName Entity name
     * @param string $query      Search query
     *
     * @return object[]
     * @throws \Darvin\AdminBundle\Search\SearchException
     */
    public function search($entityName, $query)
    {
        $searchableMeta = $this->getSearchableEntitiesMeta();

        if (!isset($searchableMeta[$entityName])) {
            throw new SearchException(sprintf('Entity "%s" does not exist or is not searchable.', $entityName));
        }

        $meta = $searchableMeta[$entityName];

        $qb = $this->em->getRepository($meta->getEntityClass())->createQueryBuilder('o');

        $searchableFields = $this->getSearchableFields($meta);

        try {
            $this->filterer->filter($qb, array_fill_keys($searchableFields, $query), [
                'non_strict_comparison_fields' => $searchableFields,
            ], false);
        } catch (FiltererException $ex) {
            throw new SearchException(
                sprintf('Unable to search for "%s" entities: "%s".', $meta->getEntityClass(), $ex->getMessage())
            );
        }
        try {
            return $qb->getQuery()->getResult();
        } catch (QueryException $ex) {
            throw new SearchException(
                sprintf('Unable to search for "%s" entities: "%s".', $meta->getEntityClass(), $ex->getMessage())
            );
        }
    }

    /**
     * @return string[]
     */
    public function getSearchableEntityNames()
    {
        $meta = $this->getSearchableEntitiesMeta();

        return array_keys($meta);
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private function getSearchableEntitiesMeta()
    {
        if (null === $this->searchableEntitiesMeta) {
            $this->searchableEntitiesMeta = [];

            foreach ($this->metadataManager->getAllMetadata() as $meta) {
                $searchableFields = $this->getSearchableFields($meta);

                if (!empty($searchableFields)) {
                    $this->searchableEntitiesMeta[$meta->getEntityName()] = $meta;
                }
            }
        }

        return $this->searchableEntitiesMeta;
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
