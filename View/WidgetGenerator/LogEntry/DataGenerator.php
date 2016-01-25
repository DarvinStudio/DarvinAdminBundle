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
use Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;

/**
 * Log entry data view widget generator
 */
class DataGenerator extends AbstractWidgetGenerator
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
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     */
    public function setObjectNamer(ObjectNamerInterface $objectNamer)
    {
        $this->objectNamer = $objectNamer;
    }

    /**
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     */
    public function setStringifier(StringifierInterface $stringifier)
    {
        $this->stringifier = $stringifier;
    }

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

        $data = $entity->getData();

        if (empty($data)) {
            return '';
        }

        $mappings = $this->getMappings($entity->getObjectClass());

        $translationPrefix = $this->getTranslationPrefix($entity->getObjectClass());

        $viewData = array();

        foreach ($data as $property => $value) {
            if (isset($mappings[$property])) {
                $value = $this->stringifier->stringify(
                    $value,
                    isset($mappings[$property]['targetEntity']) ? Type::SIMPLE_ARRAY : $mappings[$property]['type']
                );
            }

            $viewData[$translationPrefix.StringsUtil::toUnderscore($property)] = $value;
        }

        return $this->render($options, array(
            'data' => $viewData,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'log_entry_data';
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
     * @return array
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    private function getMappings($entityClass)
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getMappings();
        }
        try {
            return $this->em->getClassMetadata($entityClass)->fieldMappings;
        } catch (MappingException $ex) {
            throw new WidgetGeneratorException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
        }
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     */
    private function getTranslationPrefix($entityClass)
    {
        if ($this->metadataManager->hasMetadata($entityClass)) {
            return $this->metadataManager->getMetadata($entityClass)->getEntityTranslationPrefix();
        }

        $entityName = $this->objectNamer->name($entityClass);

        return preg_match('/_translation$/', $entityName)
            ? preg_replace('/_translation$/', '.entity.', $entityName)
            : 'log.object.'.$this->objectNamer->name($entityClass).'.property.';
    }
}
