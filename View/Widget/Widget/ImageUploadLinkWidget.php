<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Image upload link view widget
 */
class ImageUploadLinkWidget extends AbstractWidget
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
    protected function createContent($entity, array $options, $property)
    {
        $url = $this->uploadStorage->resolveUri($entity, $options['file_property']);

        return $url
            ? $this->render($options, [
                'entity' => $entity,
                'url'    => $url,
            ])
            : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('file_property')
            ->setAllowedTypes('file_property', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
