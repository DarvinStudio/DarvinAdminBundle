<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\Utils\Form\Type\TripleboxType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter form type
 */
class FilterType extends AbstractFormType
{
    private const FIELD_TYPE_MAP = [
        CheckboxType::class => TripleboxType::class,
        TextareaType::class => TextType::class,
    ];

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

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
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker  Authorization checker
     * @param \Symfony\Component\Form\FormRegistryInterface                                $formRegistry          Form registry
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface                $translationJoiner     Translation joiner
     * @param array                                                                        $defaultFieldOptions   Default field options
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FormRegistryInterface $formRegistry,
        TranslationJoinerInterface $translationJoiner,
        array $defaultFieldOptions
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formRegistry = $formRegistry;
        $this->translationJoiner = $translationJoiner;
        $this->defaultFieldOptions = $defaultFieldOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $meta = $this->getMetadata($options);

        $this->addFields($builder, $meta->getConfiguration()['form']['filter']['fields'], $meta, $options);

        if (null !== $options['parent_entity_association_param']
            && (null === $options['fields'] || isset($options['fields'][$options['parent_entity_association_param']]))
        ) {
            $builder->add($options['parent_entity_association_param'], HiddenType::class, [
                'label' => false,
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $parentEntityAssociationParam = $options['parent_entity_association_param'];

        if (null !== $parentEntityAssociationParam) {
            $field = $view->children[$parentEntityAssociationParam];

            $field->vars['full_name'] = $parentEntityAssociationParam;
            $field->vars['value']     = $options['parent_entity_id'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'csrf_protection'                 => false,
                'fields'                          => null,
                'method'                          => 'get',
                'parent_entity_association_param' => null,
                'parent_entity_id'                => null,
                'required'                        => false,
                'translation_domain'              => 'admin',
            ])
            ->setRequired('metadata')
            ->setAllowedTypes('metadata', Metadata::class)
            ->setAllowedTypes('fields', ['array', 'null']);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_filter';
    }

    /**
     * {@inheritDoc}
     */
    protected function getEntityTranslationPrefix(array $options): string
    {
        return $this->getMetadata($options)->getEntityTranslationPrefix();
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Form builder
     * @param array                                        $fields  Fields
     * @param \Darvin\AdminBundle\Metadata\Metadata        $meta    Metadata
     * @param array                                        $options Options
     *
     * @throws \InvalidArgumentException
     */
    private function addFields(FormBuilderInterface $builder, array $fields, Metadata $meta, array $options): void
    {
        $mappings = $meta->getMappings();

        foreach ($fields as $field => $attr) {
            if (null !== $options['fields']) {
                if (!isset($options['fields'][$field])) {
                    continue;
                }

                $attr = $options['fields'][$field];
            }

            $property = preg_replace('/(From|To)$/', '', $field);

            if (!isset($mappings[$property])) {
                $message = sprintf(
                    'Property "%s::$%s" is not mapped field or association.',
                    $meta->getEntityClass(),
                    $property
                );

                throw new \InvalidArgumentException($message);
            }
            if (null !== $attr['condition'] && !$this->authorizationChecker->isGranted(new Expression($attr['condition']))) {
                continue;
            }

            $fieldType    = null;
            $fieldOptions = $this->resolveFieldOptionValues($attr['options']);

            if (null !== $attr['type']) {
                $fieldType = $attr['type'];
            }
            if (null === $options['fields']) {
                $guess = isset($mappings[$property]['translation']) && $mappings[$property]['translation']
                    ? $this->formRegistry->getTypeGuesser()->guessType($meta->getTranslationClass(), $property)
                    : $this->formRegistry->getTypeGuesser()->guessType($meta->getEntityClass(), $property);

                $fieldOptions = array_merge([
                    'required' => false,
                ], $fieldOptions);

                if (null === $fieldType) {
                    $fieldType = $guess->getType();

                    if (isset(self::FIELD_TYPE_MAP[$fieldType])) {
                        $fieldType = self::FIELD_TYPE_MAP[$fieldType];
                    }

                    $fieldOptions = array_merge($this->getDefaultFieldOptions($fieldType), $fieldOptions);
                }
            }

            $builder->add($field, $fieldType, $fieldOptions);
        }
    }

    /**
     * @param string $fieldType Field type
     *
     * @return array
     */
    private function getDefaultFieldOptions(string $fieldType): array
    {
        $options           = isset($this->defaultFieldOptions[$fieldType]) ? $this->defaultFieldOptions[$fieldType] : [];
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
    private function getMetadata(array $options): Metadata
    {
        return $options['metadata'];
    }
}
