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

use Darvin\AdminBundle\Metadata\FieldBlacklistManager;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Widget\WidgetPool;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
     * @var \Darvin\AdminBundle\Metadata\FieldBlacklistManager
     */
    protected $fieldBlacklistManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    protected $stringifier;

    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    protected $widgetPool;

    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface   $container             DI container
     * @param \Darvin\AdminBundle\Metadata\FieldBlacklistManager          $fieldBlacklistManager Field blacklist manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor      Property accessor
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface      $stringifier           Stringifier
     * @param \Darvin\AdminBundle\View\Widget\WidgetPool                  $widgetPool            View widget pool
     */
    public function __construct(
        ContainerInterface $container,
        FieldBlacklistManager $fieldBlacklistManager,
        PropertyAccessorInterface $propertyAccessor,
        StringifierInterface $stringifier,
        WidgetPool $widgetPool
    ) {
        $this->container = $container;
        $this->fieldBlacklistManager = $fieldBlacklistManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->stringifier = $stringifier;
        $this->widgetPool = $widgetPool;

        $this->expressionLanguage = new ExpressionLanguage();
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
        if (isset($fieldAttr['widget'])) {
            $widgetAlias = $fieldAttr['widget']['alias'];

            return $this->widgetPool->getWidget($widgetAlias)->getContent(
                $entity,
                $fieldAttr['widget']['options'],
                $fieldName
            );
        }
        if (isset($fieldAttr['service'])) {
            $method = $fieldAttr['service']['method'];

            return $this->container->get($fieldAttr['service']['id'])->$method($entity, $fieldAttr['service']['options']);
        }
        if (isset($fieldAttr['callback'])) {
            $class = $fieldAttr['callback']['class'];
            $method = $fieldAttr['callback']['method'];

            return $class::$method($entity, $fieldAttr['callback']['options']);
        }

        $content = $this->propertyAccessor->getValue($entity, $fieldName);

        return isset($mappings[$fieldName]['type'])
            ? $this->stringifier->stringify($content, $mappings[$fieldName]['type'])
            : $content;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param object                                $entity   Entity
     * @param string                                $viewType View type
     *
     * @throws \RuntimeException
     */
    protected function validateConfiguration(Metadata $meta, $entity, $viewType)
    {
        $configuration = $meta->getConfiguration();

        foreach ($configuration['view'][$viewType]['fields'] as $field => $attr) {
            if (!$this->isPropertyViewField($meta, $viewType, $field)) {
                continue;
            }
            if (!$this->propertyAccessor->isReadable($entity, $field)) {
                throw new \RuntimeException(sprintf('Property "%s::$%s" is not readable.', $meta->getEntityClass(), $field));
            }
        }
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param string                                $viewType View type
     * @param string                                $field    Field name
     *
     * @return bool
     */
    protected function isPropertyViewField(Metadata $meta, $viewType, $field)
    {
        $config = $meta->getConfiguration()['view'][$viewType]['fields'][$field];

        return !isset($config['widget']) && !isset($config['service']) && !isset($config['callback']);
    }

    /**
     * @param array  $fieldAttr Field attributes
     * @param object $entity    Entity
     *
     * @return bool
     */
    protected function isFieldContentHidden(array $fieldAttr, $entity)
    {
        return !empty($fieldAttr['condition']) && !$this->expressionLanguage->evaluate($fieldAttr['condition'], ['entity' => $entity]);
    }
}
