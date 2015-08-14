<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14.08.15
 * Time: 15:33
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationInterface;
use Darvin\ConfigBundle\Parameter\ParameterModel;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type
 */
class ConfigurationType extends AbstractType
{
    /**
     * @var array
     */
    private static $fieldTypes = array(
        ParameterModel::TYPE_ARRAY   => 'collection',
        ParameterModel::TYPE_BOOL    => 'checkbox',
        ParameterModel::TYPE_FLOAT   => 'number',
        ParameterModel::TYPE_INTEGER => 'integer',
    );

    /**
     * @var array
     */
    private static $fieldOptions = array(
        ParameterModel::TYPE_ARRAY => array(
            'allow_add'    => true,
            'allow_delete' => true,
        ),
        ParameterModel::TYPE_BOOL => array(
            'required' => false,
        ),
    );

    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationInterface
     */
    private $configuration;

    /**
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationInterface $configuration Configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->configuration->getModel() as $parameterModel) {
            $builder->add(
                lcfirst(StringsUtil::toCamelCase($parameterModel->getName())),
                $this->getFieldType($parameterModel),
                $this->getFieldOptions($parameterModel)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => get_class($this->configuration),
            'intention'          => md5(__FILE__.$this->configuration->getName()),
            'translation_domain' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_configuration';
    }

    /**
     * @param \Darvin\ConfigBundle\Parameter\ParameterModel $parameterModel Parameter model
     *
     * @return string
     */
    private function getFieldType(ParameterModel $parameterModel)
    {
        $parameterOptions = $parameterModel->getOptions();

        if (isset($parameterOptions['form']['type'])) {
            return $parameterOptions['form']['type'];
        }
        if (isset(self::$fieldTypes[$parameterModel->getType()])) {
            return self::$fieldTypes[$parameterModel->getType()];
        }

        return null;
    }

    /**
     * @param \Darvin\ConfigBundle\Parameter\ParameterModel $parameterModel Parameter model
     *
     * @return array
     */
    private function getFieldOptions(ParameterModel $parameterModel)
    {
        $fieldOptions = array();

        $parameterOptions = $parameterModel->getOptions();

        if (isset($parameterOptions['form']['options'])) {
            $fieldOptions = $parameterOptions['form']['options'];
        } elseif (isset(self::$fieldOptions[$parameterModel->getType()])) {
            $fieldOptions = self::$fieldOptions[$parameterModel->getType()];
        }
        if (!array_key_exists('label', $fieldOptions)) {
            $fieldOptions['label'] = sprintf(
                'configuration.%s.parameter.%s',
                $this->configuration->getName(),
                $parameterModel->getName()
            );
        }

        return $fieldOptions;
    }
}
