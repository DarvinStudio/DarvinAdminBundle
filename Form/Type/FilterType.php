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
        'checkbox' => 'choice',
        'textarea' => 'text',
    );

    /**
     * @var array
     */
    private static $defaultFieldOptions = array(
        'checkbox' => array(
            'choices' => array(
                'boolean.0',
                'boolean.1',
            ),
        ),
        'date' => array(
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
        ),
        'datetime' => array(
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy HH:mm',
        ),
        'time' => array(
            'widget' => 'single_text',
        ),
    );

    /**
     * @var \Symfony\Component\Form\FormTypeGuesserInterface
     */
    private $formTypeGuesser;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $meta;

    /**
     * @param \Symfony\Component\Form\FormTypeGuesserInterface $formTypeGuesser Form type guesser
     * @param \Darvin\AdminBundle\Metadata\Metadata            $meta            Metadata
     */
    public function __construct(FormTypeGuesserInterface $formTypeGuesser, Metadata $meta)
    {
        $this->formTypeGuesser = $formTypeGuesser;
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
            $builder->add($options['parent_entity_association'], 'hidden', array(
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
    public function getName()
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

                if (isset(self::$defaultFieldOptions[$type])) {
                    $options = array_merge(self::$defaultFieldOptions[$type], $options);
                }
                if (isset(self::$fieldTypeChangeMap[$type])) {
                    $type = self::$fieldTypeChangeMap[$type];
                }
            }

            $builder->add($field, $type, $options);
        }
    }
}
