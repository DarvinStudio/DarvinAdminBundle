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

use Darvin\AdminBundle\Metadata\FieldBlacklistManager;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Base form type
 */
class BaseType extends AbstractFormType
{
    /**
     * @var \Darvin\AdminBundle\Metadata\FieldBlacklistManager
     */
    private $fieldBlacklistManager;

    /**
     * @var \Symfony\Component\Form\FormRegistryInterface
     */
    private $formRegistry;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @var array
     */
    private $defaultFieldOptions;

    /**
     * @param \Darvin\AdminBundle\Metadata\FieldBlacklistManager              $fieldBlacklistManager Field blacklist manager
     * @param \Symfony\Component\Form\FormRegistryInterface                   $formRegistry          Form registry
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager   Translatable manager
     * @param array                                                           $defaultFieldOptions   Default field options
     */
    public function __construct(
        FieldBlacklistManager $fieldBlacklistManager,
        FormRegistryInterface $formRegistry,
        TranslatableManagerInterface $translatableManager,
        array $defaultFieldOptions
    ) {
        $this->fieldBlacklistManager = $fieldBlacklistManager;
        $this->formRegistry = $formRegistry;
        $this->translatableManager = $translatableManager;
        $this->defaultFieldOptions = $defaultFieldOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $meta = $this->getMetadata($options);
        $configuration = $this->getMetadata($options)->getConfiguration();

        $fields = $configuration['form'][$options['action_type']]['fields'];

        foreach ($configuration['form'][$options['action_type']]['field_groups'] as $groupFields) {
            $fields = array_merge($fields, $groupFields);
        }

        $fieldFilterProvided = isset($options['field_filter']);

        foreach ($fields as $field => $attr) {
            if (($fieldFilterProvided && $field !== $options['field_filter'])
                || $this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, sprintf('[form][%s]', $options['action_type']))
            ) {
                continue;
            }

            $fieldType = $attr['type'];
            $fieldOptions = $this->resolveFieldOptionValues($attr['options']);

            if (empty($fieldType)) {
                $guess = $this->guessFieldType($field, $options['data_class']);

                if (!empty($guess)) {
                    $fieldType = $guess->getType();
                    $fieldOptions = array_merge($guess->getOptions(), $fieldOptions);
                }
            }
            if (isset($this->defaultFieldOptions[$fieldType])) {
                $fieldOptions = array_merge($this->defaultFieldOptions[$fieldType], $fieldOptions);
            }
            if ($this->translatableManager->isTranslatable($meta->getEntityClass())
                && $field === $this->translatableManager->getTranslationsProperty()
            ) {
                $this->addValidConstraint($fieldOptions);
            }

            $builder->add($field, $fieldType, $fieldOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_token_id'      => md5(__FILE__),
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
     * {@inheritdoc}
     */
    protected function getEntityTranslationPrefix(array $options)
    {
        return $this->getMetadata($options)->getEntityTranslationPrefix();
    }

    /**
     * @param array $fieldOptions Field options
     */
    private function addValidConstraint(array &$fieldOptions)
    {
        if (!isset($fieldOptions['constraints'])) {
            $fieldOptions['constraints'] = new Valid();

            return;
        }
        if (is_array($fieldOptions['constraints'])) {
            $fieldOptions['constraints'][] = new Valid();

            return;
        }

        $fieldOptions['constraints'] = [
            $fieldOptions['constraints'],
            new Valid(),
        ];
    }

    /**
     * @param string $field       Field name
     * @param string $entityClass Entity class
     *
     * @return \Symfony\Component\Form\Guess\TypeGuess
     */
    private function guessFieldType($field, $entityClass)
    {
        if (!$this->translatableManager->isTranslatable($entityClass)) {
            return null;
        }

        $guess = $this->formRegistry->getTypeGuesser()->guessType($entityClass, $field);

        return $guess->getConfidence() > 0
            ? $guess
            : $this->formRegistry->getTypeGuesser()->guessType($this->translatableManager->getTranslationClass($entityClass), $field);
    }

    /**
     * @param array $options Form options
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    private function getMetadata(array $options)
    {
        return $options['metadata'];
    }
}
