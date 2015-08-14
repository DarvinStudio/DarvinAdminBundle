<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14.08.15
 * Time: 15:23
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationPool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configurations form type
 */
class ConfigurationsType extends AbstractType
{
    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationPool
     */
    private $configurationPool;

    /**
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationPool $configurationPool Configuration pool
     */
    public function __construct(ConfigurationPool $configurationPool)
    {
        $this->configurationPool = $configurationPool;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->configurationPool->getAll() as $configuration) {
            $builder->add($configuration->getName(), new ConfigurationType($configuration), array(
                'label' => sprintf('configuration.%s.title', $configuration->getName()),
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true,
            'data_class'         => get_class($this->configurationPool),
            'intention'          => md5(__FILE__),
            'translation_domain' => 'admin',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_configurations';
    }
}
