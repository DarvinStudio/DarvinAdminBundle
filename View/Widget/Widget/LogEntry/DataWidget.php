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
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;

/**
 * Log entry data view widget
 */
class DataWidget extends AbstractWidget
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $objectNamer;

    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    private $stringifier;

    /**
     * @param \Doctrine\ORM\EntityManager                            $em          Entity manager
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface         $objectNamer Object namer
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     */
    public function __construct(EntityManager $em, ObjectNamerInterface $objectNamer, StringifierInterface $stringifier)
    {
        $this->em = $em;
        $this->objectNamer = $objectNamer;
        $this->stringifier = $stringifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return 'log_entry_data';
    }

    /**
     * @param \Darvin\AdminBundle\Entity\LogEntry $logEntry Log entry
     * @param array                               $options  Options
     *
     * @return string|null
     */
    protected function createContent($logEntry, array $options): ?string
    {
        $data = $logEntry->getData();

        if (empty($data)) {
            return null;
        }

        $mappings          = $this->getMappings($logEntry->getObjectClass());
        $translationPrefix = $this->getTranslationPrefix($logEntry->getObjectClass());
        $viewData          = [];

        foreach ($data as $property => $value) {
            if (isset($mappings[$property])) {
                $value = $this->stringifier->stringify(
                    $value,
                    isset($mappings[$property]['targetEntity']) ? Types::SIMPLE_ARRAY : $mappings[$property]['type']
                );
            }

            $viewData[$translationPrefix.StringsUtil::toUnderscore($property)] = $value;
        }

        return $this->render([
            'data' => $viewData,
        ]);
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
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getMappings(string $entityClass): array
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getMappings();
        }
        try {
            return $this->em->getClassMetadata($entityClass)->fieldMappings;
        } catch (MappingException $ex) {
            throw new \InvalidArgumentException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
        }
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     */
    private function getTranslationPrefix(string $entityClass): string
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getEntityTranslationPrefix();
        }

        $entityName = $this->objectNamer->name($entityClass);

        if (preg_match('/_translation$/', $entityName)) {
            return preg_replace('/_translation$/', '.entity.', $entityName);
        }

        return sprintf('log.object.%s.property.', $this->objectNamer->name($entityClass));
    }
}
