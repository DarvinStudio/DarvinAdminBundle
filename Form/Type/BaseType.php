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

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\Utils\Strings\StringsUtil;
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

            $fieldOptions = $this->resolveFieldOptionValues($attr['options']);

            if (!array_key_exists('label', $fieldOptions)) {
                $fieldOptions['label'] = $translationPrefix.StringsUtil::toUnderscore($field);
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
            'intention'          => md5(__FILE__.$this->getName().$this->meta->getEntityClass()),
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

    /**
     * @param array $options Field options
     *
     * @return array
     */
    private function resolveFieldOptionValues(array $options)
    {
        foreach ($options as &$value) {
            if (!is_array($value)) {
                continue;
            }
            if (is_callable($value)) {
                $value = $value();

                continue;
            }

            $value = $this->resolveFieldOptionValues($value);
        }

        unset($value);

        return $options;
    }
}
