<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
