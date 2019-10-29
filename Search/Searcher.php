<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Search;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ContentBundle\Filterer\FiltererInterface;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\QueryException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Searcher
 */
class Searcher implements SearcherInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\ContentBundle\Filterer\FiltererInterface
     */
    private $filterer;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private $searchableEntitiesMeta;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Doctrine\ORM\EntityManager                                                  $em                   Entity manager
     * @param \Darvin\ContentBundle\Filterer\FiltererInterface                             $filterer             Filterer
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface                   $metadataManager      Metadata manager
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface                $translationJoiner    Translation joiner
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManager $em,
        FiltererInterface $filterer,
        AdminMetadataManagerInterface $metadataManager,
        TranslationJoinerInterface $translationJoiner
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
        $this->filterer = $filterer;
        $this->metadataManager = $metadataManager;
        $this->translationJoiner = $translationJoiner;

        $this->searchableEntitiesMeta = null;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $entityName, string $query): array
    {
        $meta = $this->getSearchableEntityMeta($entityName);

        $qb = $this->em->getRepository($meta->getEntityClass())->createQueryBuilder('o');

        if ($this->translationJoiner->isTranslatable($meta->getEntityClass())) {
            $this->translationJoiner->joinTranslation($qb, true, null, null, true);
        }

        $searchableFields = $this->getSearchableFields($meta);

        try {
            $this->filterer->filter($qb, array_fill_keys($searchableFields, $query), [
                'non_strict_comparison_fields' => $searchableFields,
            ], false);
        } catch (\Exception $ex) {
            throw new \RuntimeException(
                sprintf('Unable to search for "%s" entities: "%s".', $meta->getEntityClass(), $ex->getMessage())
            );
        }
        try {
            return $qb->getQuery()->getResult();
        } catch (QueryException $ex) {
            throw new \RuntimeException(
                sprintf('Unable to search for "%s" entities: "%s".', $meta->getEntityClass(), $ex->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntityMeta(string $entityName): Metadata
    {
        $allMeta = $this->getSearchableEntitiesMeta();

        if (!isset($allMeta[$entityName])) {
            throw new \InvalidArgumentException(sprintf('Entity "%s" is not searchable.', $entityName));
        }

        return $allMeta[$entityName];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntityNames(): array
    {
        return array_keys($this->getSearchableEntitiesMeta());
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchable(string $entityName): bool
    {
        $allMeta = $this->getSearchableEntitiesMeta();

        return isset($allMeta[$entityName]);
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private function getSearchableEntitiesMeta(): array
    {
        if (null === $this->searchableEntitiesMeta) {
            $this->searchableEntitiesMeta = [];

            foreach ($this->metadataManager->getAllMetadata() as $meta) {
                if (!$this->authorizationChecker->isGranted(Permission::EDIT, $meta->getEntityClass())
                    && !$this->authorizationChecker->isGranted(Permission::VIEW, $meta->getEntityClass())
                ) {
                    continue;
                }

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
    private function getSearchableFields(Metadata $meta): array
    {
        $config = $meta->getConfiguration();

        return $config['searchable_fields'];
    }
}
