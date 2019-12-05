<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Identifier accessor
 */
class IdentifierAccessor implements IdentifierAccessorInterface
{
    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface  $metadataManager  Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(AdminMetadataManagerInterface $metadataManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getId($entity)
    {
        $meta = $this->metadataManager->getMetadata($entity);

        $identifier = $meta->getIdentifier();

        if (!$this->propertyAccessor->isReadable($entity, $identifier)) {
            throw new MetadataException(
                sprintf('Identifier "%s::$%s" is not readable.', ClassUtils::getClass($entity), $identifier)
            );
        }

        return $this->propertyAccessor->getValue($entity, $identifier);
    }
}
