<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationPool;
use Darvin\ConfigBundle\Form\Type\Configuration\ConfigurationType;
use Darvin\Utils\Security\Authorization\AccessibilityChecker;
use Darvin\Utils\Security\SecurableInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Configurations form type
 */
class ConfigurationsType extends AbstractType
{
    const CONFIGURATIONS_TYPE_CLASS = __CLASS__;

    /**
     * @var \Darvin\Utils\Security\Authorization\AccessibilityChecker
     */
    private $accessibilityChecker;

    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationPool
     */
    private $configurationPool;

    /**
     * @param \Darvin\Utils\Security\Authorization\AccessibilityChecker $accessibilityChecker Accessibility checker
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationPool      $configurationPool    Configuration pool
     */
    public function __construct(AccessibilityChecker $accessibilityChecker, ConfigurationPool $configurationPool)
    {
        $this->accessibilityChecker = $accessibilityChecker;
        $this->configurationPool = $configurationPool;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->configurationPool->getAllConfigurations() as $configuration) {
            if ($configuration instanceof SecurableInterface
                && !$this->accessibilityChecker->isAccessible($configuration)
            ) {
                continue;
            }

            $builder->add($configuration->getName(), ConfigurationType::CONFIGURATION_TYPE_CLASS, array(
                'label'         => sprintf('configuration.%s.title', $configuration->getName()),
                'configuration' => $configuration,
                'constraints'   => new Valid(),
                'data_class'    => get_class($configuration),
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
            'data_class'    => get_class($this->configurationPool),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_configurations';
    }
}
