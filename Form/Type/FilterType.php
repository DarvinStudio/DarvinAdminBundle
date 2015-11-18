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
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
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
    private static $fieldTypeChangeMap = array(
        'Symfony\Component\Form\Extension\Core\Type\CheckboxType' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'Symfony\Component\Form\Extension\Core\Type\TextareaType' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
    );

    /**
     * @var \Symfony\Component\Form\FormTypeGuesserInterface
     */
    private $formTypeGuesser;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $meta;

    /**
     * @param \Symfony\Component\Form\FormTypeGuesserInterface              $formTypeGuesser   Form type guesser
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner Translation joiner
     * @param \Darvin\AdminBundle\Metadata\Metadata                         $meta              Metadata
     */
    public function __construct(
        FormTypeGuesserInterface $formTypeGuesser,
        TranslationJoinerInterface $translationJoiner,
        Metadata $meta
    ) {
        $this->formTypeGuesser = $formTypeGuesser;
        $this->translationJoiner = $translationJoiner;
        $this->meta = $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configuration = $this->meta->getConfiguration();

        foreach ($configuration['form']['filter']['field_groups'] as $fields) {
            $this->addFields($builder, $fields);
        }

        $this->addFields($builder, $configuration['form']['filter']['fields']);

        if (!empty($options['parent_entity_association'])) {
            $builder->add($options['parent_entity_association'], 'Symfony\Component\Form\Extension\Core\Type\HiddenType', array(
                'label' => false,
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $parentEntityAssociation = $options['parent_entity_association'];

        if (empty($parentEntityAssociation)) {
            return;
        }

        $field = $view->children[$parentEntityAssociation];
        $field->vars['full_name'] = $parentEntityAssociation;
        $field->vars['value'] = $options['parent_entity_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'           => false,
            'method'                    => 'get',
            'parent_entity_association' => null,
            'parent_entity_id'          => null,
            'required'                  => false,
            'translation_domain'        => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->meta->getFilterFormTypeName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getMetadata()
    {
        return $this->meta;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder Form builder
     * @param array                                        $fields  Fields
     *
     * @throws \Darvin\AdminBundle\Form\FormException
     */
    private function addFields(FormBuilderInterface $builder, array $fields)
    {
        $mappings = $this->meta->getMappings();

        foreach ($fields as $field => $attr) {
            $property = preg_replace('/(From|To)$/', '', $field);

            if (!isset($mappings[$property])) {
                $message = sprintf(
                    'Property "%s::$%s" is not mapped field or association.',
                    $this->meta->getEntityClass(),
                    $property
                );

                throw new FormException($message);
            }

            $options = $this->resolveFieldOptionValues($attr['options']);
            $typeGuess = isset($mappings[$property]['translation']) && $mappings[$property]['translation']
                ? $this->formTypeGuesser->guessType($this->meta->getTranslationClass(), $property)
                : $this->formTypeGuesser->guessType($this->meta->getEntityClass(), $property);
            $options = array_merge(array(
                'required' => false,
            ), $typeGuess->getOptions(), $options);

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
        $translationJoiner = $this->translationJoiner;

        switch ($fieldType) {
            case 'Symfony\Component\Form\Extension\Core\Type\ChoiceType':
                return array(
                    'choices' => array(
                        'boolean.0',
                        'boolean.1',
                    ),
                );
            case 'Symfony\Component\Form\Extension\Core\Type\DateType':
                return array(
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                );
            case 'Symfony\Component\Form\Extension\Core\Type\DateTimeType':
                return array(
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy HH:mm',
                );
            case 'Symfony\Bridge\Doctrine\Form\Type\EntityType':
                return array(
                    'query_builder' => function (EntityRepository $er) use ($translationJoiner) {
                        $qb = $er->createQueryBuilder('o');

                        if ($translationJoiner->isTranslatable($er->getClassName())) {
                            $translationJoiner->joinTranslation($qb, null, 'translations');
                            $qb->addSelect('translations');
                        }

                        return $qb;
                    },
                );
            case 'Symfony\Component\Form\Extension\Core\Type\TimeType':
                return array(
                    'widget' => 'single_text',
                );
            default:
                return array();
        }
    }
}
