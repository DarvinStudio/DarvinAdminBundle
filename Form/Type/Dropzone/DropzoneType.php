<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type\Dropzone;

use Darvin\AdminBundle\Form\FormException;
use Darvin\Utils\Strings\StringsUtil;
use Oneup\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;

/**
 * Dropzone form type
 */
class DropzoneType extends AbstractType
{
    const DEFAULT_ACCEPTED_FILES         = 'image/*';
    const DEFAULT_ONEUP_UPLOADER_MAPPING = 'darvin';

    const OPTION_UPLOADABLE_FIELD = 'uploadable_field';

    /**
     * @var \Oneup\UploaderBundle\Templating\Helper\UploaderHelper
     */
    private $oneupUploaderHelper;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Vich\UploaderBundle\Metadata\MetadataReader
     */
    private $vichUploaderMetadataReader;

    /**
     * @var array
     */
    private $oneupUploaderConfig;

    /**
     * @var int
     */
    private $uploadMaxSizeMB;

    /**
     * @param \Oneup\UploaderBundle\Templating\Helper\UploaderHelper      $oneupUploaderHelper        1-up uploader helper
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor           Property accessor
     * @param \Vich\UploaderBundle\Metadata\MetadataReader                $vichUploaderMetadataReader Vich uploader metadata reader
     * @param array                                                       $oneupUploaderConfig        1-up uploader configuration
     * @param int                                                         $uploadMaxSizeMB            Max upload file size in MB
     */
    public function __construct(
        UploaderHelper $oneupUploaderHelper,
        PropertyAccessorInterface $propertyAccessor,
        MetadataReader $vichUploaderMetadataReader,
        array $oneupUploaderConfig,
        $uploadMaxSizeMB
    ) {
        $this->oneupUploaderHelper = $oneupUploaderHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->vichUploaderMetadataReader = $vichUploaderMetadataReader;
        $this->oneupUploaderConfig = $oneupUploaderConfig;
        $this->uploadMaxSizeMB = $uploadMaxSizeMB;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->validateOptions($options);

        $propertyAccessor = $this->propertyAccessor;
        $tmpDir = $this->oneupUploaderConfig['mappings'][$options['oneup_uploader_mapping']]['storage']['directory'];
        $uploadableClass = $options['uploadable_class'];
        $uploadableField = $this->getUploadableField($options);
        $uploadablesField = $builder->getName();

        $builder
            ->add('dropzone', 'Symfony\Component\Form\Extension\Core\Type\FormType', array(
                'label'  => false,
                'mapped' => false,
                'attr'   => array(
                    'class'               => 'dropzone',
                    'data-accepted-files' => $options['accepted_files'],
                    'data-files'          => '.files',
                    'data-max-filesize'   => $this->uploadMaxSizeMB,
                    'data-url'            => $this->oneupUploaderHelper->endpoint($options['oneup_uploader_mapping']),
                ),
            ))
            ->add('files', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', array(
                'label'     => false,
                'mapped'    => false,
                'type'      => FileType::FILE_TYPE_CLASS,
                'allow_add' => true,
                'options'   => array(
                    'label' => false,
                ),
                'attr' => array(
                    'class'         => 'files',
                    'data-autoinit' => 0,
                ),
            ))
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'accepted_files'         => self::DEFAULT_ACCEPTED_FILES,
                'intention'              => md5(__FILE__.$this->getBlockPrefix()),
                'mapped'                 => false,
                'oneup_uploader_mapping' => self::DEFAULT_ONEUP_UPLOADER_MAPPING,
            ))
            ->setDefined(array(
                'accepted_files',
                self::OPTION_UPLOADABLE_FIELD,
            ))
            ->setRequired(array(
                'uploadable_class',
            ))
            ->setAllowedTypes('oneup_uploader_mapping', 'string')
            ->setAllowedTypes('uploadable_class', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_dropzone';
    }

    /**
     * @param array $options Form options
     *
     * @return string
     * @throws \Darvin\AdminBundle\Form\FormException
     */
    private function getUploadableField(array $options)
    {
        $uploadableClass = $options['uploadable_class'];

        $uploadableFields = array_keys($this->vichUploaderMetadataReader->getUploadableFields($uploadableClass));

        if (empty($uploadableFields)) {
            throw new FormException(sprintf('Class "%s" has no uploadable fields.', $uploadableClass));
        }
        if (isset($options[self::OPTION_UPLOADABLE_FIELD])) {
            if (!in_array($options[self::OPTION_UPLOADABLE_FIELD], $uploadableFields)) {
                $message = sprintf(
                    'Uploadable field "%s" does not exist in class "%s", existing uploadable fields: "%s".',
                    $options[self::OPTION_UPLOADABLE_FIELD],
                    $uploadableClass,
                    implode('", "', $uploadableFields)
                );

                throw new FormException($message);
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

                throw new FormException($message);
            }

            $uploadableField = $uploadableFields[0];
        }

        $uploadable = new $uploadableClass();

        if (!$this->propertyAccessor->isWritable($uploadable, $uploadableField)) {
            throw new FormException(
                sprintf('Uploadable field "%s::$%s" is not writable.', $uploadableClass, $uploadableField)
            );
        }

        return $uploadableField;
    }

    /**
     * @param array $options Form options
     *
     * @throws \Darvin\AdminBundle\Form\FormException
     */
    private function validateOptions(array $options)
    {
        $oneupUploaderMapping = $options['oneup_uploader_mapping'];

        if (!isset($this->oneupUploaderConfig['mappings'][$oneupUploaderMapping])) {
            $message = sprintf(
                '1-up uploader mapping "%s" does not exist, existing mappings: "%s".',
                $oneupUploaderMapping,
                implode('", "', array_keys($this->oneupUploaderConfig['mappings']))
            );

            throw new FormException($message);
        }

        $uploadableClass = $options['uploadable_class'];

        if (!class_exists($uploadableClass)) {
            throw new FormException(sprintf('Uploadable class "%s" does not exist.', $uploadableClass));
        }
        if (!$this->vichUploaderMetadataReader->isUploadable($uploadableClass)) {
            throw new FormException(sprintf('Class "%s" is not valid uploadable class.', $uploadableClass));
        }
    }
}
