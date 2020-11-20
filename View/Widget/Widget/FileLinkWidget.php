<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\FileBundle\Entity\AbstractFile;
use Doctrine\Common\Util\ClassUtils;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * File link view widget
 */
class FileLinkWidget extends AbstractWidget
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    private $uploaderStorage;

    /**
     * @param \Vich\UploaderBundle\Storage\StorageInterface $uploaderStorage Uploader storage
     */
    public function __construct(StorageInterface $uploaderStorage)
    {
        $this->uploaderStorage = $uploaderStorage;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        $file = null !== $options['property'] ? $this->getPropertyValue($entity, $options['property']) : $entity;

        if (null === $file) {
            return null;
        }
        if (!is_object($file)) {
            throw new \InvalidArgumentException(sprintf('File must be object, "%s" provided.', gettype($file)));
        }
        if (!$file instanceof AbstractFile) {
            throw new \InvalidArgumentException(
                sprintf('File object "%s" must be instance of "%s".', ClassUtils::getClass($file), AbstractFile::class)
            );
        }

        $url = $this->uploaderStorage->resolveUri($file, AbstractFile::PROPERTY_FILE);

        if (null === $url) {
            return null;
        }

        return sprintf('<a href="%1$s" target="_blank">%1$s</a>', $url);
    }
}
