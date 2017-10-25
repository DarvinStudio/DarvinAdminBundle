<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber as BaseTranslatableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Translatable event subscriber
 */
class TranslatableSubscriber extends BaseTranslatableSubscriber
{
    /**
     * @var \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var string
     */
    private $translatableTrait;

    /**
     * @var string
     */
    private $translationTrait;

    /**
     * @var int
     */
    private $translatableFetchMode;

    /**
     * @var int
     */
    private $translationFetchMode;

    /**
     * @var array
     */
    private $entityOverride;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer,
        callable $currentLocaleCallable = null,
        callable $defaultLocaleCallable = null,
        $translatableTrait,
        $translationTrait,
        $translatableFetchMode,
        $translationFetchMode,
        array $entityOverride
    ) {
        parent::__construct(
            $classAnalyzer,
            $currentLocaleCallable,
            $defaultLocaleCallable,
            $translatableTrait,
            $translationTrait,
            $translatableFetchMode,
            $translationFetchMode
        );

        $this->classAnalyzer = $classAnalyzer;
        $this->translatableTrait = $translatableTrait;
        $this->translationTrait = $translationTrait;

        $this->translatableFetchMode = constant(ClassMetadata::class.'::FETCH_'.$translatableFetchMode);
        $this->translationFetchMode  = constant(ClassMetadata::class.'::FETCH_'.$translationFetchMode);

        $this->entityOverride = $entityOverride;
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $meta */
        $meta = $args->getClassMetadata();

        if ($this->isTranslatable($meta)) {
            $this->mapTranslatable($meta);
        }
        if ($this->isTranslation($meta)) {
            $this->mapTranslation($meta);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta Metadata
     */
    private function mapTranslatable(ClassMetadata $meta)
    {
        if (!$meta->hasAssociation('translations')) {
            $class = $meta->getName();

            if (isset($this->entityOverride[$class])) {
                $class = $this->entityOverride[$class];
            }

            $translation = $class::getTranslationEntityClass();

            if (isset($this->entityOverride[$translation])) {
                $translation = $this->entityOverride[$translation];
            }

            $meta->mapOneToMany([
                'fieldName'     => 'translations',
                'targetEntity'  => $translation,
                'mappedBy'      => 'translatable',
                'cascade'       => ['persist', 'merge', 'remove'],
                'orphanRemoval' => true,
                'fetch'         => $this->translatableFetchMode,
                'indexBy'       => 'locale',
            ]);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta Metadata
     */
    private function mapTranslation(ClassMetadata $meta)
    {
        if (!$meta->hasField('id')) {
            (new ClassMetadataBuilder($meta))->createField('id', 'integer')->generatedValue('IDENTITY')->makePrimaryKey()->build();

            $meta->setIdGenerator(new IdentityGenerator());
        }
        if (!$meta->hasAssociation('translatable')) {
            $class = $meta->getName();

            if (isset($this->entityOverride[$class])) {
                $class = $this->entityOverride[$class];
            }

            $translatable = $class::getTranslatableEntityClass();

            if (isset($this->entityOverride[$translatable])) {
                $translatable = $this->entityOverride[$translatable];
            }

            $meta->mapManyToOne([
                'fieldName'    => 'translatable',
                'targetEntity' => $translatable,
                'inversedBy'   => 'translations',
                'cascade'      => ['persist', 'merge'],
                'fetch'        => $this->translationFetchMode,
                'joinColumns'  => [
                    [
                        'name'                 => 'translatable_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'CASCADE',
                    ],
                ],
            ]);
        }

        $constraint = $meta->getTableName().'_unique_translation';

        if (!isset($meta->table['uniqueConstraints'][$constraint])) {
            $meta->table['uniqueConstraints'][$constraint] = [
                'columns' => ['translatable_id', 'locale'],
            ];
        }
        if (!($meta->hasField('locale') || $meta->hasAssociation('locale'))) {
            $meta->mapField([
                'fieldName' => 'locale',
                'type'      => 'string',
            ]);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta Metadata
     *
     * @return bool
     */
    private function isTranslatable(ClassMetadata $meta)
    {
        return $this->getClassAnalyzer()->hasTrait($meta->getReflectionClass(), $this->translatableTrait, true);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta Metadata
     *
     * @return bool
     */
    private function isTranslation(ClassMetadata $meta)
    {
        return $this->getClassAnalyzer()->hasTrait($meta->getReflectionClass(), $this->translationTrait, true);
    }
}
