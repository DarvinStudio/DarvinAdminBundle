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

use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface;
use Darvin\ConfigBundle\Configuration\ConfigurationInterface;
use Darvin\ConfigBundle\Configuration\ConfigurationPool;
use Darvin\ConfigBundle\Form\Type\Configuration\ConfigurationType;
use Darvin\ImageBundle\DarvinImageBundle;
use Darvin\ImageBundle\Size\Size;
use Darvin\Utils\Security\Authorization\AccessibilityChecker;
use Darvin\Utils\Security\SecurableInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Configurations form type
 */
class ConfigurationsType extends AbstractType
{
    /**
     * @var array
     */
    private static $interfaces = [
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $interface = self::$interfaces[$options['config_type']];

        $configurations = $this->configurationPool->getAllConfigurations();

        foreach ($configurations as $key => $configuration) {
            if (empty($interface)) {
                foreach (self::$interfaces as $otherInterface) {
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
            if ($configuration instanceof SecurableInterface
                && !$this->accessibilityChecker->isAccessible($configuration)
            ) {
                continue;
            }

            $builder->add($configuration->getName(), ConfigurationType::class, [
                'label'         => $configuration instanceof SecurityConfigurationInterface
                    ? false
                    : sprintf('configuration.%s.title', $configuration->getName())
                ,
                'configuration' => $configuration,
                'constraints'   => new Valid(),
                'data_class'    => get_class($configuration),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (DarvinImageBundle::MAJOR_VERSION >= 6) {
            $this->hideImageSizes($view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
                'data_class'    => get_class($this->configurationPool),
            ])
            ->setRequired('config_type')
            ->setAllowedTypes('config_type', 'string')
            ->setAllowedValues('config_type', array_keys(self::$interfaces));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_configurations';
    }

    /**
     * @param \Symfony\Component\Form\FormView $view Form view
     */
    private function hideImageSizes(FormView $view)
    {
        foreach ($view->children as $name => $configuration) {
            if ('_token' === $name) {
                continue;
            }

            $hideConfiguration = true;

            foreach ($configuration->children as $parameter) {
                $data = $parameter->vars['data'];

                if (empty($data) || !is_array($data)) {
                    $hideConfiguration = false;

                    continue;
                }
                foreach ($data as $value) {
                    if (!$value instanceof Size) {
                        $hideConfiguration = false;

                        continue 2;
                    }
                }

                $parameter->vars['hidden'] = true;
            }
            if ($hideConfiguration) {
                $configuration->vars['hidden'] = true;
            }
        }
    }
}
