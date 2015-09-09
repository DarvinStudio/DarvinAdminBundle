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
use Darvin\AdminBundle\View\WidgetGenerator\AbstractWidgetGenerator;

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

        return $this->metadataManager->hasMetadata($entity->getObjectClass())
            ? $this->render($options, array(
                'entity_class' => $entity->getObjectClass(),
                'entity_id'    => $entity->getObjectId(),
                'entity_name'  => $this->metadataManager->getByEntityClass($entity->getObjectClass())->getEntityName(),
            ))
            : $entity->getObjectClass();
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
}
