<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget\LogEntry;

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;

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
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function __construct(ObjectNamerInterface $objectNamer)
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * {@inheritDoc}
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
        return sprintf('entity_name.single.%s', $this->getEntityName($logEntry->getObjectClass()));
    }

    /**
     * {@inheritDoc}
     */
    protected function getAllowedEntityClasses(): iterable
    {
        yield LogEntry::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
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
        if (is_a($entityClass, TranslationInterface::class, true)) {
            /** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface $entityClass */
            $translatableClass = $entityClass::getTranslatableEntityClass();

            if ($this->metadataManager->hasMetadata($translatableClass)) {
                return $this->metadataManager->getMetadata($translatableClass)->getEntityName().'_translation';
            }
        }

        return $this->objectNamer->name($entityClass);
    }
}
