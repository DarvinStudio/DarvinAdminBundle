<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget\LogEntry;

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;

/**
 * Log entry entity name view widget
 */
class EntityNameWidget extends AbstractWidget
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $objectNamer;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function setObjectNamer(ObjectNamerInterface $objectNamer)
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     */
    public function setTranslatableManager(TranslatableManagerInterface $translatableManager)
    {
        $this->translatableManager = $translatableManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'log_entry_entity_name';
    }

    /**
     * @param \Darvin\AdminBundle\Entity\LogEntry $logEntry Log entry
     * @param array                               $options  Options
     *
     * @return string
     */
    protected function createContent($logEntry, array $options): ?string
    {
        return 'entity_name.single.'.$this->getEntityName($logEntry->getObjectClass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedEntityClasses(): array
    {
        return [
            LogEntry::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): array
    {
        return [
            Permission::VIEW,
        ];
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     */
    private function getEntityName(string $entityClass): string
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getEntityName();
        }
        if ($this->translatableManager->isTranslation($entityClass)) {
            /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translation $entityClass */
            $translatableClass = $entityClass::getTranslatableEntityClass();

            if ($this->metadataManager->hasMetadata($translatableClass)) {
                return $this->metadataManager->getMetadata($translatableClass)->getEntityName().'_translation';
            }
        }

        return $this->objectNamer->name($entityClass);
    }
}
