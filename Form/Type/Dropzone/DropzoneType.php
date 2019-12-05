<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Dropzone;

use Darvin\ImageBundle\Size\SizeDescriber;
use Darvin\Utils\Strings\StringsUtil;
use Oneup\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;

/**
 * Dropzone form type
 */
class DropzoneType extends AbstractType
{
    private const DEFAULT_ONEUP_UPLOADER_MAPPING = 'darvin_admin';
    private const OPTION_UPLOADABLE_FIELD        = 'uploadable_field';

    /**
     * @var \Oneup\UploaderBundle\Templating\Helper\UploaderHelper
     */
    private $oneupUploaderHelper;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Vich\UploaderBundle\Metadata\MetadataReader
     */
    private $vichUploaderMetadataReader;

    /**
     * @var array
     */
    private $constraints;

    /**
     * @var array
     */
    private $oneupUploaderConfig;

    /**
     * @var int
     */
    private $uploadMaxSizeMB;

    /**
     * @var \Darvin\ImageBundle\Size\SizeDescriber|null
     */
    private $imageSizeDescriber;

    /**
     * @param \Oneup\UploaderBundle\Templating\Helper\UploaderHelper      $oneupUploaderHelper        1-up uploader helper
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor           Property accessor
     * @param \Symfony\Contracts\Translation\TranslatorInterface          $translator                 Translator
     * @param \Vich\UploaderBundle\Metadata\MetadataReader                $vichUploaderMetadataReader Vich uploader metadata reader
     * @param array                                                       $constraints                Constraints
     * @param array                                                       $oneupUploaderConfig        1-up uploader configuration
     * @param int                                                         $uploadMaxSizeMB            Max upload file size in MB
     * @param \Darvin\ImageBundle\Size\SizeDescriber|null                 $imageSizeDescriber         Image size describer
     */
    public function __construct(
        UploaderHelper $oneupUploaderHelper,
        PropertyAccessorInterface $propertyAccessor,
        TranslatorInterface $translator,
        MetadataReader $vichUploaderMetadataReader,
        array $constraints,
        array $oneupUploaderConfig,
        $uploadMaxSizeMB,
        SizeDescriber $imageSizeDescriber = null
    ) {
        $this->oneupUploaderHelper = $oneupUploaderHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->translator = $translator;
        $this->vichUploaderMetadataReader = $vichUploaderMetadataReader;
        $this->constraints = $constraints;
        $this->oneupUploaderConfig = $oneupUploaderConfig;
        $this->uploadMaxSizeMB = $uploadMaxSizeMB;
        $this->imageSizeDescriber = $imageSizeDescriber;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->validateOptions($options);

        $propertyAccessor = $this->propertyAccessor;
        $tmpDir           = $this->oneupUploaderConfig['mappings'][$options['oneup_uploader_mapping']]['storage']['directory'];
        $uploadableClass  = $options['uploadable_class'];
        $uploadableField  = $this->getUploadableField($options);
        $uploadablesField = $builder->getName();

        $builder
            ->add('dropzone', FormType::class, [
                'label'  => false,
                'mapped' => false,
            ])
            ->add('files', CollectionType::class, [
                'label'         => false,
                'mapped'        => false,
                'allow_add'     => true,
                'entry_type'    => FileType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'attr' => [
                    'data-autoinit' => 0,
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($propertyAccessor, $uploadablesField) {
                $event->setData($propertyAccessor->getValue($event->getForm()->getParent()->getData(), $uploadablesField));
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use (
                $propertyAccessor,
                $tmpDir,
                $uploadableClass,
                $uploadableField,
                $uploadablesField
            ) {
                $data = $event->getData();

                if (empty($data['files'])) {
                    return;
                }

                $object = $event->getForm()->getParent()->getData();

                $uploadables = $propertyAccessor->getValue($object, $uploadablesField);

                foreach ($data['files'] as $fileInfo) {
                    $tmpPathname = $tmpDir.DIRECTORY_SEPARATOR.$fileInfo['filename'];

                    $file = new UploadedFile($tmpPathname, $fileInfo['originalFilename'], null, null, null, true);

                    $uploadable = new $uploadableClass();
                    $propertyAccessor->setValue($uploadable, $uploadableField, $file);

                    $uploadables->add($uploadable);
                }

                $setter = 'set'.StringsUtil::toCamelCase($uploadablesField);

                $object->$setter($uploadables);
            });
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_merge($view->vars, [
            'disableable' => $options['disableable'],
            'editable'    => $options['editable'],
        ]);

        if (null !== $this->imageSizeDescriber && null === $view->vars['help']) {
            $view->vars['help'] = $this->imageSizeDescriber->describeSize(
                $options['image_filters'],
                $options['image_width'],
                $options['image_height'],
                $options['uploadable_class']
            );

            if (null !== $view->vars['help']) {
                $view->vars['help'] .= '<br>';
            }

            $view->vars['help'] .= $this->translator->trans('form.file.help', [
                '%size%' => $this->uploadMaxSizeMB,
            ], 'admin');
        }

        $attr = [
            'class'               => 'dropzone',
            'data-accepted-files' => $options['accepted_files'],
            'data-files'          => $view->children['files']->vars['id'],
            'data-max-filesize'   => $this->uploadMaxSizeMB,
            'data-url'            => $this->oneupUploaderHelper->endpoint($options['oneup_uploader_mapping']),
        ];

        foreach ($this->constraints as $name => $value) {
            if (is_scalar($value)) {
                $attr[sprintf('data-constraint-%s', str_replace('_', '-', $name))] = $value;
            }
        }

        $view->children['dropzone']->vars['attr'] = array_merge($view->children['dropzone']->vars['attr'], $attr);

        /** @var \Symfony\Component\Form\FormError $error */
        foreach ($view->vars['errors'] as $error) {
            $cause = $error->getCause();

            if (!$cause instanceof ConstraintViolationInterface) {
                continue;
            }

            $file = $cause->getInvalidValue();

            if (!$file instanceof UploadedFile) {
                continue;
            }
            foreach ($view->children['files'] as $key => $field) {
                $data = $field->vars['data'];

                if ($data['filename'] === $file->getFilename() && $data['originalFilename'] === $file->getClientOriginalName()) {
                    unset($view->children['files'][$key]);

                    @unlink($file->getPathname());
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'accepted_files'         => implode(',', $this->constraints['mime_types']),
                'csrf_token_id'          => md5(__FILE__.$this->getBlockPrefix()),
                'error_bubbling'         => false,
                'image_filters'          => [],
                'image_width'            => 0,
                'image_height'           => 0,
                'mapped'                 => false,
                'disableable'            => true,
                'editable'               => true,
                'oneup_uploader_mapping' => self::DEFAULT_ONEUP_UPLOADER_MAPPING,
            ])
            ->setDefined([
                'accepted_files',
                self::OPTION_UPLOADABLE_FIELD,
            ])
            ->setRequired([
                'uploadable_class',
            ])
            ->setAllowedTypes('disableable', 'boolean')
            ->setAllowedTypes('editable', 'boolean')
            ->setAllowedTypes('oneup_uploader_mapping', 'string')
            ->setAllowedTypes('uploadable_class', 'string')
            ->setAllowedTypes('image_filters', [
                'array',
                'null',
                'string',
            ])
            ->setAllowedTypes('image_width', 'integer')
            ->setAllowedTypes('image_height', 'integer');
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_dropzone';
    }

    /**
     * @param array $options Form options
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getUploadableField(array $options): string
    {
        $uploadableClass = $options['uploadable_class'];

        $uploadableFields = array_keys($this->vichUploaderMetadataReader->getUploadableFields($uploadableClass));

        if (empty($uploadableFields)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" has no uploadable fields.', $uploadableClass));
        }
        if (isset($options[self::OPTION_UPLOADABLE_FIELD])) {
            if (!in_array($options[self::OPTION_UPLOADABLE_FIELD], $uploadableFields)) {
                $message = sprintf(
                    'Uploadable field "%s" does not exist in class "%s", existing uploadable fields: "%s".',
                    $options[self::OPTION_UPLOADABLE_FIELD],
                    $uploadableClass,
                    implode('", "', $uploadableFields)
                );

                throw new \InvalidArgumentException($message);
            }

            $uploadableField = $options[self::OPTION_UPLOADABLE_FIELD];
        } else {
            if (count($uploadableFields) > 1) {
                $message = sprintf(
                    'Class "%s" has more than one uploadable field ("%s") - "%s" form option required.',
                    $uploadableClass,
                    implode('", "', $uploadableFields),
                    self::OPTION_UPLOADABLE_FIELD
                );

                throw new \InvalidArgumentException($message);
            }

            $uploadableField = $uploadableFields[0];
        }

        $uploadable = new $uploadableClass();

        if (!$this->propertyAccessor->isWritable($uploadable, $uploadableField)) {
            throw new \InvalidArgumentException(
                sprintf('Uploadable field "%s::$%s" is not writable.', $uploadableClass, $uploadableField)
            );
        }

        return $uploadableField;
    }

    /**
     * @param array $options Form options
     *
     * @throws \InvalidArgumentException
     */
    private function validateOptions(array $options): void
    {
        $oneupUploaderMapping = $options['oneup_uploader_mapping'];

        if (!isset($this->oneupUploaderConfig['mappings'][$oneupUploaderMapping])) {
            $message = sprintf(
                '1-up uploader mapping "%s" does not exist, existing mappings: "%s".',
                $oneupUploaderMapping,
                implode('", "', array_keys($this->oneupUploaderConfig['mappings']))
            );

            throw new \InvalidArgumentException($message);
        }

        $uploadableClass = $options['uploadable_class'];

        if (!class_exists($uploadableClass)) {
            throw new \InvalidArgumentException(sprintf('Uploadable class "%s" does not exist.', $uploadableClass));
        }
        if (!$this->vichUploaderMetadataReader->isUploadable($uploadableClass)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not valid uploadable class.', $uploadableClass));
        }
    }
}
