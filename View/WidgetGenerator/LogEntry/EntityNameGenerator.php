<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator\LogEntry;

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\WidgetGenerator\AbstractWidgetGenerator;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;

/**
 * Log entry entity name view widget generator
 */
class EntityNameGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $objectNamer;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function setObjectNamer(ObjectNamerInterface $objectNamer)
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'log_entry_entity_name';
    }

    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, $property, array $options)
    {
        return 'log.object.'.$this->getEntityName($entity->getObjectClass()).'.title';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredEntityClass()
    {
        return LogEntry::LOG_ENTRY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return array(
            Permission::VIEW,
        );
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     */
    private function getEntityName($entityClass)
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getEntityName();
        }

        return $this->objectNamer->name($entityClass);
    }
}
