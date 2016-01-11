<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Image upload link generator
 */
class ImageUploadLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    private $uploadStorage;

    /**
     * @param \Vich\UploaderBundle\Storage\StorageInterface $uploadStorage Upload storage
     */
    public function setUploadStorage(StorageInterface $uploadStorage)
    {
        $this->uploadStorage = $uploadStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        $this->validate($entity, $options);

        if (!$this->isGranted(Permission::VIEW, $entity)) {
            return '';
        }

        return $this->render($options, array(
            'entity' => $entity,
            'url'    => $this->uploadStorage->resolveUri($entity, $options['file_property']),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'image_upload_link';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('file_property')
            ->setAllowedTypes('file_property', 'string');
    }
}
