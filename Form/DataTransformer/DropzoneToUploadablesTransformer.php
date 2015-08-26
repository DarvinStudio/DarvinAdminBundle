<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Dropzone to uploadables data transformer
 */
class DropzoneToUploadablesTransformer implements DataTransformerInterface
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var string
     */
    private $tmpFilesDir;

    /**
     * @var string
     */
    private $uploadableClass;

    /**
     * @var string
     */
    private $uploadableField;

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param string                                                      $tmpFilesDir      Temporary files directory
     * @param string                                                      $uploadableClass  Uploadable class
     * @param string                                                      $uploadableField  Uploadable field
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        $tmpFilesDir,
        $uploadableClass,
        $uploadableField
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->tmpFilesDir = $tmpFilesDir;
        $this->uploadableClass = $uploadableClass;
        $this->uploadableField = $uploadableField;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ('' === $value) {
            return null;
        }

        $uploadables = array();

        $dropzoneFiles = $value['files'];

        if (empty($dropzoneFiles)) {
            return array();
        }

        $uploadableClass = $this->uploadableClass;

        /** @var \Darvin\AdminBundle\Dropzone\DropzoneFile $dropzoneFile */
        foreach ($dropzoneFiles as $dropzoneFile) {
            $pathname = $this->tmpFilesDir.DIRECTORY_SEPARATOR.$dropzoneFile->getFilename();
            $file = new UploadedFile($pathname, $dropzoneFile->getOriginalFilename(), null, null, null, true);

            $uploadable = new $uploadableClass();
            $this->propertyAccessor->setValue($uploadable, $this->uploadableField, $file);

            $uploadables[] = $uploadable;
        }

        return $uploadables;
    }
}
