<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 12:43
 */

namespace Darvin\AdminBundle\View;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Stringifier\Stringifier;
use Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Entity to view transformer abstract implementation
 */
abstract class AbstractEntityToViewTransformer
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Darvin\AdminBundle\Stringifier\Stringifier
     */
    protected $stringifier;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    protected $widgetGeneratorPool;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface    $container           DI container
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                 $metadataManager     Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface  $propertyAccessor    Property accessor
     * @param \Darvin\AdminBundle\Stringifier\Stringifier                  $stringifier         Stringifier
     * @param \Symfony\Component\Translation\TranslatorInterface           $translator          Translator
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool $widgetGeneratorPool View widget generator pool
     */
    public function __construct(
        ContainerInterface $container,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        Stringifier $stringifier,
        TranslatorInterface $translator,
        WidgetGeneratorPool $widgetGeneratorPool
    )
    {
        $this->container = $container;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->stringifier = $stringifier;
        $this->translator = $translator;
        $this->widgetGeneratorPool = $widgetGeneratorPool;
    }

    /**
     * @param object $entity    Entity
     * @param string $fieldName Field name
     * @param array  $fieldAttr Field attributes
     * @param array  $mappings  Mappings
     *
     * @return string
     */
    protected function getFieldContent($entity, $fieldName, array $fieldAttr, array $mappings)
    {
        if (empty($fieldAttr)) {
            $content = $this->propertyAccessor->getValue($entity, $fieldName);

            return $this->stringifier->stringify($content, $mappings[$fieldName]['type']);
        }
        if (isset($fieldAttr['callback'])) {
            $class = $fieldAttr['callback']['class'];
            $method = $fieldAttr['callback']['method'];

            return $class::$method($entity, $fieldAttr['callback']['options']);
        }
        if (isset($fieldAttr['service'])) {
            $method = $fieldAttr['service']['method'];

            return $this->container->get($fieldAttr['service']['id'])->$method($entity, $fieldAttr['service']['options']);
        }

        $widgetGeneratorAlias = $fieldAttr['widget_generator']['alias'];

        return $this->widgetGeneratorPool->get($widgetGeneratorAlias)->generate(
            $entity,
            $fieldAttr['widget_generator']['options']
        );
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param object                                $entity   Entity
     * @param string                                $viewType View type
     *
     * @throws \Darvin\AdminBundle\View\ViewException
     */
    protected function validateConfiguration(Metadata $meta, $entity, $viewType)
    {
        $configuration = $meta->getConfiguration();
        $mappings = $meta->getMappings();

        foreach ($configuration['view'][$viewType]['fields'] as $field => $attr) {
            if (!empty($attr)) {
                continue;
            }
            if (!isset($mappings[$field])) {
                throw new ViewException(
                    sprintf('Property "%s::$%s" does not exist or is not mapped.', $meta->getEntityClass(), $field)
                );
            }
            if (!$this->propertyAccessor->isReadable($entity, $field)) {
                $message = sprintf(
                    'Property "%s::$%s" is not readable. Make sure it has public access.',
                    $meta->getEntityClass(),
                    $field
                );

                throw new ViewException($message);
            }
        }
    }
}
