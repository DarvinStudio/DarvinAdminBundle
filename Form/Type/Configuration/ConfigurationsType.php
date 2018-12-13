<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface;
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
    private const INTERFACES = [
        'common'   => null,
        'security' => SecurityConfigurationInterface::class,
    ];

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $configurations = $this->configurationPool->getAllConfigurations();
        $interface      = self::INTERFACES[$options['config_type']];

        foreach ($configurations as $key => $configuration) {
            if (empty($interface)) {
                foreach (self::INTERFACES as $otherInterface) {
                    if (!empty($otherInterface) && $configuration instanceof $otherInterface) {
                        unset($configurations[$key]);
                    }
                }

                continue;
            }
            if (!$configuration instanceof $interface) {
                unset($configurations[$key]);
            }
        }
        foreach ($configurations as $configuration) {
            if ($configuration instanceof SecurableInterface && !$this->accessibilityChecker->isAccessible($configuration)) {
                continue;
            }

            $builder->add($configuration->getName(), ConfigurationType::class, [
                'configuration' => $configuration,
                'constraints'   => new Valid(),
                'data_class'    => get_class($configuration),
                'label'         => $configuration instanceof SecurityConfigurationInterface
                    ? false
                    : sprintf('configuration.%s.title', $configuration->getName())
                ,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
                'data_class'    => get_class($this->configurationPool),
            ])
            ->setRequired('config_type')
            ->setAllowedTypes('config_type', 'string')
            ->setAllowedValues('config_type', array_keys(self::INTERFACES));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_configurations';
    }
}
