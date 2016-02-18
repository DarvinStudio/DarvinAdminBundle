<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    protected $stringifier;

    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    protected $widgetGeneratorPool;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface    $container           DI container
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface  $propertyAccessor    Property accessor
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface       $stringifier         Stringifier
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool $widgetGeneratorPool View widget generator pool
     */
    public function __construct(
        ContainerInterface $container,
        PropertyAccessorInterface $propertyAccessor,
        StringifierInterface $stringifier,
        WidgetGeneratorPool $widgetGeneratorPool
    ) {
        $this->container = $container;
        $this->propertyAccessor = $propertyAccessor;
        $this->stringifier = $stringifier;
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

            return isset($mappings[$fieldName]['type'])
                ? $this->stringifier->stringify($content, $mappings[$fieldName]['type'])
                : $content;
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

        $widgetGeneratorAlias = $fieldAttr['widget']['alias'];

        return $this->widgetGeneratorPool->getWidgetGenerator($widgetGeneratorAlias)->generate(
            $entity,
            $fieldAttr['widget']['options'],
            $fieldName
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

        foreach ($configuration['view'][$viewType]['fields'] as $field => $attr) {
            if (!empty($attr)) {
                continue;
            }
            if (!$this->propertyAccessor->isReadable($entity, $field)) {
                throw new ViewException(sprintf('Property "%s::$%s" is not readable.', $meta->getEntityClass(), $field));
            }
        }
    }
}
