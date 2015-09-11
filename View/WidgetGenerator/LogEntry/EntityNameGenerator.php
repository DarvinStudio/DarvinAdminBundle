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
use Darvin\Utils\Strings\StringsUtil;

/**
 * Log entry entity name view widget generator
 */
class EntityNameGenerator extends AbstractWidgetGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        /** @var \Darvin\AdminBundle\Entity\LogEntry $entity */
        $this->validate($entity, $options);

        if (!$this->isGranted(Permission::VIEW, $entity)) {
            return '';
        }

        return LogEntry::OBJECT_NAME_PREFIX.$this->getEntityName($entity->getObjectClass());
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
    protected function getRequiredEntityClass()
    {
        return LogEntry::LOG_ENTRY_CLASS;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     */
    private function getEntityName($entityClass)
    {
        if ($this->metadataManager->hasMetadataForEntityClass($entityClass)) {
            return $this->metadataManager->getByEntityClass($entityClass)->getEntityName();
        }

        $parts = explode('\\', $entityClass);
        $offset = array_search('Entity', $parts);

        if ($offset) {
            $parts = array_slice($parts, $offset + 1);
        }

        return StringsUtil::toUnderscore(implode('_', $parts));
    }
}
