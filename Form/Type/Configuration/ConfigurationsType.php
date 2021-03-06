<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface;
use Darvin\ConfigBundle\Form\Type\ConfigurationType;
use Darvin\Utils\Security\Authorization\AccessibilityCheckerInterface;
use Darvin\Utils\Security\SecurableInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configurations form type
 */
class ConfigurationsType extends AbstractType
{
    /**
     * @var \Darvin\Utils\Security\Authorization\AccessibilityCheckerInterface
     */
    private $accessibilityChecker;

    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface
     */
    private $configurationPool;

    /**
     * @param \Darvin\Utils\Security\Authorization\AccessibilityCheckerInterface $accessibilityChecker Accessibility checker
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface      $configurationPool    Configuration pool
     */
    public function __construct(AccessibilityCheckerInterface $accessibilityChecker, ConfigurationPoolInterface $configurationPool)
    {
        $this->accessibilityChecker = $accessibilityChecker;
        $this->configurationPool = $configurationPool;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $configurations = $this->configurationPool->getAllConfigurations();

        foreach ($configurations as $configuration) {
            $options = $configuration->getOptions();

            if (isset($options['hidden']) && $options['hidden']) {
                continue;
            }
            if ($configuration instanceof SecurableInterface && !$this->accessibilityChecker->isAccessible($configuration)) {
                continue;
            }

            $builder->add($configuration->getName(), ConfigurationType::class, [
                'label'         => sprintf('configuration.%s.title', $configuration->getName()),
                'configuration' => $configuration,
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children as $name => $child) {
            if (empty($child->children)) {
                unset($view->children[$name]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => get_class($this->configurationPool),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_configurations';
    }
}
