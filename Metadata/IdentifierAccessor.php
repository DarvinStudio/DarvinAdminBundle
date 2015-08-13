<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 05.08.15
 * Time: 10:04
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
        $meta = $this->metadataManager->getByEntity($entity);
        $identifier = $meta->getIdentifier();

        if (!$this->propertyAccessor->isReadable($entity, $identifier)) {
            $message = sprintf(
                'Identifier "%s::$%s" is not readable. Make sure it has public access.',
                ClassUtils::getClass($entity),
                $identifier
            );

            throw new MetadataException($message);
        }

        return $this->propertyAccessor->getValue($entity, $identifier);
    }
}
