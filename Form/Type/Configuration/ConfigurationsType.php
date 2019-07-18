<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface;
use Darvin\ConfigBundle\Form\Type\ConfigurationType;
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
     * @var \Darvin\Utils\Security\Authorization\AccessibilityChecker
     */
    private $accessibilityChecker;

    /**
     * @var \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface
     */
    private $configurationPool;

    /**
     * @param \Darvin\Utils\Security\Authorization\AccessibilityChecker     $accessibilityChecker Accessibility checker
     * @param \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface $configurationPool    Configuration pool
     */
    public function __construct(AccessibilityChecker $accessibilityChecker, ConfigurationPoolInterface $configurationPool)
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

        foreach ($configurations as $configuration) {
            if ($configuration instanceof SecurableInterface && !$this->accessibilityChecker->isAccessible($configuration)) {
                continue;
            }

            $builder->add($configuration->getName(), ConfigurationType::class, [
                'label'         => sprintf('configuration.%s.title', $configuration->getName()),
                'configuration' => $configuration,
                'constraints'   => new Valid(),
                'data_class'    => get_class($configuration),
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children as $name => $child) {
            if ('_token' !== $name && empty($child->children)) {
                unset($view->children[$name]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => md5(__FILE__.$this->getBlockPrefix()),
            'data_class'    => get_class($this->configurationPool),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_configurations';
    }
}
