<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
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
class IdentifierAccessor
{
    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager  Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(MetadataManager $metadataManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param object $entity Entity
     *
     * @return int
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getValue($entity)
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
