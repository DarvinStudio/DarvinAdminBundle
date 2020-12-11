<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Metadata\Metadata;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Entity form type
 */
class EntityType extends AbstractFormType
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\Form\FormRegistryInterface
     */
    private $formRegistry;

    /**
     * @var array
     */
    private $defaultFieldOptions;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker  Authorization checker
     * @param \Symfony\Component\Form\FormRegistryInterface                                $formRegistry          Form registry
     * @param array                                                                        $defaultFieldOptions   Default field options
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FormRegistryInterface $formRegistry,
        array $defaultFieldOptions
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formRegistry = $formRegistry;
        $this->defaultFieldOptions = $defaultFieldOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config         = $this->getMetadata($options)->getConfiguration();
        $filterProvided = isset($options['field_filter']);

        foreach ($config['form'][$options['action_type']]['fields'] as $field => $attr) {
            if (($filterProvided && $field !== $options['field_filter'])
                || (null !== $attr['condition'] && !$this->authorizationChecker->isGranted(new Expression($attr['condition']), $builder->getData()))
            ) {
                continue;
            }

            $fieldType    = $attr['type'];
            $fieldOptions = $this->resolveFieldOptionValues($attr['options']);

            if (null === $fieldType) {
                $guess = $this->guessFieldType($field, $options['data_class']);

                if (null !== $guess) {
                    $fieldType    = $guess->getType();
                    $fieldOptions = array_merge($guess->getOptions(), $fieldOptions);
                }
            }
            if (isset($this->defaultFieldOptions[$fieldType])) {
                $fieldOptions = array_merge($this->defaultFieldOptions[$fieldType], $fieldOptions);
            }

            $builder->add($field, $fieldType, $fieldOptions);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'csrf_protection'    => false,
                'translation_domain' => 'admin',
            ])
            ->remove('data_class')
            ->setRequired([
                'action_type',
                'data_class',
                'metadata',
            ])
            ->setDefined('field_filter')
            ->setAllowedTypes('action_type', 'string')
            ->setAllowedTypes('data_class', 'string')
            ->setAllowedTypes('field_filter', 'string')
            ->setAllowedTypes('metadata', Metadata::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_entity';
    }

    /**
     * {@inheritDoc}
     */
    protected function getEntityTranslationPrefix(array $options): string
    {
        return $this->getMetadata($options)->getEntityTranslationPrefix();
    }

    /**
     * @param string $field       Field name
     * @param string $entityClass Entity class
     *
     * @return \Symfony\Component\Form\Guess\TypeGuess|null
     */
    private function guessFieldType(string $field, string $entityClass): ?TypeGuess
    {
        if (!is_a($entityClass, TranslatableInterface::class, true)) {
            return null;
        }

        $guess = $this->formRegistry->getTypeGuesser()->guessType($entityClass, $field);

        /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $entityClass */
        $translationClass = $entityClass::getTranslationEntityClass();

        return $guess->getConfidence() > 0
            ? $guess
            : $this->formRegistry->getTypeGuesser()->guessType($translationClass, $field);
    }

    /**
     * @param array $options Form options
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    private function getMetadata(array $options): Metadata
    {
        return $options['metadata'];
    }
}
