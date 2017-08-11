<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Form\FormException;
use Darvin\AdminBundle\Metadata\FieldBlacklistManager;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter form type
 */
class FilterType extends AbstractFormType
{
    /**
     * @var array
     */
    private static $fieldTypeChangeMap = [
        CheckboxType::class => TriStateCheckboxType::class,
        TextareaType::class => TextType::class,
    ];

    /**
     * @var \Darvin\AdminBundle\Metadata\FieldBlacklistManager
     */
    private $fieldBlacklistManager;

    /**
     * @var \Symfony\Component\Form\FormRegistryInterface
     */
    private $formRegistry;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @var array
     */
    private $defaultFieldOptions;

    /**
     * @param \Darvin\AdminBundle\Metadata\FieldBlacklistManager            $fieldBlacklistManager Field blacklist manager
     * @param \Symfony\Component\Form\FormRegistryInterface                 $formRegistry          Form registry
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner     Translation joiner
     * @param array                                                         $defaultFieldOptions   Default field options
     */
    public function __construct(
        FieldBlacklistManager $fieldBlacklistManager,
        FormRegistryInterface $formRegistry,
        TranslationJoinerInterface $translationJoiner,
        array $defaultFieldOptions
    ) {
        $this->fieldBlacklistManager = $fieldBlacklistManager;
        $this->formRegistry = $formRegistry;
        $this->translationJoiner = $translationJoiner;
        $this->defaultFieldOptions = $defaultFieldOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $meta = $this->getMetadata($options);
        $configuration = $meta->getConfiguration();

        foreach ($configuration['form']['filter']['field_groups'] as $fields) {
            $this->addFields($builder, $fields, $meta);
        }

        $this->addFields($builder, $configuration['form']['filter']['fields'], $meta);

        if (!empty($options['parent_entity_association_param'])) {
            $builder->add($options['parent_entity_association_param'], HiddenType::class, [
                'label' => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $parentEntityAssociationParam = $options['parent_entity_association_param'];

        if (!empty($parentEntityAssociationParam)) {
            $field = $view->children[$parentEntityAssociationParam];
            $field->vars['full_name'] = $parentEntityAssociationParam;
            $field->vars['value'] = $options['parent_entity_id'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection'                 => false,
                'method'                          => 'get',
                'parent_entity_association_param' => null,
                'parent_entity_id'                => null,
                'required'                        => false,
                'translation_domain'              => 'admin',
            ])
            ->setRequired('metadata')
            ->setAllowedTypes('metadata', Metadata::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityTranslationPrefix(array $options)
    {
        return $this->getMetadata($options)->getEntityTranslationPrefix();
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Form builder
     * @param array                                        $fields  Fields
     * @param \Darvin\AdminBundle\Metadata\Metadata        $meta    Metadata
     *
     * @throws \Darvin\AdminBundle\Form\FormException
     */
    private function addFields(FormBuilderInterface $builder, array $fields, Metadata $meta)
    {
        $mappings = $meta->getMappings();

        foreach ($fields as $field => $attr) {
            $property = preg_replace('/(From|To)$/', '', $field);

            if (!isset($mappings[$property])) {
                $message = sprintf(
                    'Property "%s::$%s" is not mapped field or association.',
                    $meta->getEntityClass(),
                    $property
                );

                throw new FormException($message);
            }
            if ($this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[form][filter]')) {
                continue;
            }

            $options = $this->resolveFieldOptionValues($attr['options']);
            $typeGuess = isset($mappings[$property]['translation']) && $mappings[$property]['translation']
                ? $this->formRegistry->getTypeGuesser()->guessType($meta->getTranslationClass(), $property)
                : $this->formRegistry->getTypeGuesser()->guessType($meta->getEntityClass(), $property);
            $options = array_merge([
                'required' => false,
            ], $typeGuess->getOptions(), $options);

            if (!empty($attr['type'])) {
                $type = $attr['type'];
            } else {
                $type = $typeGuess->getType();

                if (isset(self::$fieldTypeChangeMap[$type])) {
                    $type = self::$fieldTypeChangeMap[$type];
                }

                $options = array_merge($this->getDefaultFieldOptions($type), $options);
            }

            $builder->add($field, $type, $options);
        }
    }

    /**
     * @param string $fieldType Field type
     *
     * @return array
     */
    private function getDefaultFieldOptions($fieldType)
    {
        $options = isset($this->defaultFieldOptions[$fieldType]) ? $this->defaultFieldOptions[$fieldType] : [];

        $translationJoiner = $this->translationJoiner;

        switch ($fieldType) {
            case EntityType::class:
                $options['query_builder'] = function (EntityRepository $er) use ($translationJoiner) {
                    $qb = $er->createQueryBuilder('o');

                    if ($translationJoiner->isTranslatable($er->getClassName())) {
                        $translationJoiner->joinTranslation($qb, true);
                    }

                    return $qb;
                };
        }

        return $options;
    }

    /**
     * @param array $options Form options
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    private function getMetadata(array $options)
    {
        return $options['metadata'];
    }
}
