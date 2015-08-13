<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 10.08.15
 * Time: 15:41
 */

namespace Darvin\AdminBundle\Metadata;

use Doctrine\ORM\EntityManager;
use Gedmo\Sortable\SortableListener;

/**
 * Sort criteria detector
 */
class SortCriteriaDetector
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Gedmo\Sortable\SortableListener
     */
    private $sortableListener;

    /**
     * @param \Doctrine\ORM\EntityManager                  $em               Entity manager
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager  Metadata manager
     * @param \Gedmo\Sortable\SortableListener             $sortableListener Sortable event listener
     */
    public function __construct(EntityManager $em, MetadataManager $metadataManager, SortableListener $sortableListener)
    {
        $this->em = $em;
        $this->metadataManager = $metadataManager;
        $this->sortableListener = $sortableListener;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     */
    public function detect($entityClass)
    {
        $meta = $this->metadataManager->getByEntityClass($entityClass);
        $configuration = $meta->getConfiguration();

        if (!empty($configuration['order_by'])) {
            return $configuration['order_by'];
        }

        $sortableConfiguration = $this->sortableListener->getConfiguration($this->em, $entityClass);

        return !empty($sortableConfiguration)
            ? array(
                $sortableConfiguration['position'] => 'asc',
            )
            : array(
                $meta->getIdentifier() => 'desc',
            );
    }
}
