<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EntityNamer;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Entity namer
 */
class EntityNamer implements EntityNamerInterface
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $genericObjectNamer;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var string[]
     */
    private $names;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $genericObjectNamer Generic object namer
     * @param \Darvin\AdminBundle\Metadata\MetadataManager   $metadataManager    Admin metadata manager
     */
    public function __construct(ObjectNamerInterface $genericObjectNamer, MetadataManager $metadataManager)
    {
        $this->genericObjectNamer = $genericObjectNamer;
        $this->metadataManager = $metadataManager;

        $this->names = [];
    }

    /**
     * {@inheritdoc}
     */
    public function name($entityOrClass)
    {
        $class = is_object($entityOrClass) ? ClassUtils::getClass($entityOrClass) : $entityOrClass;

        if (!isset($this->names[$class])) {
            $this->names[$class] = $this->getName($class);
        }

        return $this->names[$class];
    }

    /**
     * @param string $class Entity class
     *
     * @return string
     */
    private function getName($class)
    {
        if ($this->metadataManager->hasMetadata($class)) {
            return $this->metadataManager->getMetadata($class)->getEntityName();
        }

        return $this->genericObjectNamer->name($class);
    }
}
