<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 05.08.15
 * Time: 12:19
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Metadata\Metadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base form type
 */
class BaseType extends AbstractType
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $meta;

    /**
     * @var string
     */
    private $fieldFilter;

    /**
     * @param string                                $action      Action
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta        Metadata
     * @param string                                $fieldFilter Field filter
     */
    public function __construct($action, Metadata $meta, $fieldFilter = null)
    {
        $this->action = $action;
        $this->meta = $meta;
        $this->fieldFilter = $fieldFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configuration = $this->meta->getConfiguration();
        $translationPrefix = $this->meta->getEntityTranslationPrefix();

        $fields = $configuration['form'][$this->action]['fields'];

        foreach ($configuration['form'][$this->action]['field_groups'] as $groupFields) {
            $fields = array_merge($fields, $groupFields);
        }
        foreach ($fields as $field => $attr) {
            if (!empty($this->fieldFilter) && $field !== $this->fieldFilter) {
                continue;
            }

            $fieldOptions = $attr['options'];

            if (!array_key_exists('label', $fieldOptions)) {
                $fieldOptions['label'] = $translationPrefix.$field;
            }

            $builder->add($field, $attr['type'], $fieldOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => $this->meta->getEntityClass(),
            'intention'          => md5(__FILE__.$this->meta->getEntityClass()),
            'translation_domain' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->meta->getFormTypeName();
    }
}
