<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
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
    protected function createContent($entity, array $options): ?string
    {
        $url = $this->uploaderStorage->resolveUri(
            $entity,
            null !== $options['file_property'] ? $options['file_property'] : sprintf('%sFile', $options['property'])
        );

        if (null === $url) {
            return null;
        }

        return $this->render([
            'entity' => $entity,
            'url'    => $url,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('file_property', null)
            ->setAllowedTypes('file_property', ['string', 'null']);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
