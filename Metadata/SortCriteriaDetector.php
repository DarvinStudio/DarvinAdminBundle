<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Doctrine\ORM\EntityManager;
use Gedmo\Mapping\MappedEventSubscriber;
use Gedmo\Tree\TreeListener;

/**
 * Sort criteria detector
 */
class SortCriteriaDetector implements SortCriteriaDetectorInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Gedmo\Sortable\SortableListener
     */
    private $sortableListener;

    /**
     * @var \Gedmo\Tree\TreeListener
     */
    private $treeListener;

    /**
     * @param \Doctrine\ORM\EntityManager                                $em               Entity manager
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager  Metadata manager
     * @param \Gedmo\Mapping\MappedEventSubscriber                       $sortableListener Sortable event listener
     * @param \Gedmo\Tree\TreeListener                                   $treeListener     Tree event listener
     */
    public function __construct(
        EntityManager $em,
        AdminMetadataManagerInterface $metadataManager,
        MappedEventSubscriber $sortableListener,
        TreeListener $treeListener
    ) {
        $this->em = $em;
        $this->metadataManager = $metadataManager;
        $this->sortableListener = $sortableListener;
        $this->treeListener = $treeListener;
    }

    /**
     * {@inheritdoc}
     */
    public function detectSortCriteria(string $entityClass): array
    {
        $meta = $this->metadataManager->getMetadata($entityClass);
        $config = $meta->getConfiguration();

        if (!empty($config['order_by'])) {
            return $config['order_by'];
        }

        $criteria = [];

        $treeConfig = $this->treeListener->getConfiguration($this->em, $entityClass);

        if (!empty($treeConfig)) {
            $criteria[$treeConfig['level']] = 'asc';
        }

        $sortableConfig = $this->sortableListener->getConfiguration($this->em, $entityClass);

        if (!empty($sortableConfig)) {
            $criteria[$sortableConfig['position']] = 'asc';
        }
        if (!empty($criteria)) {
            return $criteria;
        }

        return [
            $meta->getIdentifier() => 'desc',
        ];
    }
}
