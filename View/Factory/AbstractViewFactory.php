<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory;

use Darvin\AdminBundle\Metadata\FieldBlacklistManagerInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * View factory abstract implementation
 */
abstract class AbstractViewFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Darvin\AdminBundle\Metadata\FieldBlacklistManagerInterface
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
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    protected $widgetPool;

    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage|null
     */
    private $expressionLanguage = null;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\FieldBlacklistManagerInterface $fieldBlacklistManager Field blacklist manager
     */
    public function setFieldBlacklistManager(FieldBlacklistManagerInterface $fieldBlacklistManager): void
    {
        $this->fieldBlacklistManager = $fieldBlacklistManager;
    }

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     */
    public function setStringifier(StringifierInterface $stringifier): void
    {
        $this->stringifier = $stringifier;
    }

    /**
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface $widgetPool View widget pool
     */
    public function setWidgetPool(ViewWidgetPoolInterface $widgetPool): void
    {
        $this->widgetPool = $widgetPool;
    }

    /**
     * @param object $entity    Entity
     * @param string $fieldName Field name
     * @param array  $fieldAttr Field attributes
     * @param array  $mappings  Mappings
     *
     * @return mixed
     */
    protected function getFieldContent($entity, string $fieldName, array $fieldAttr, array $mappings)
    {
        if (!empty($fieldAttr['widget'])) {
            $widgetAlias = key($fieldAttr['widget']);

            return $this->widgetPool->getWidget($widgetAlias)->getContent(
                $entity,
                $fieldAttr['widget'][$widgetAlias],
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
    protected function validateConfiguration(Metadata $meta, $entity, string $viewType): void
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
    protected function isPropertyViewField(Metadata $meta, string $viewType, string $field): bool
    {
        $config = $meta->getConfiguration()['view'][$viewType]['fields'][$field];

        return empty($config['widget']) && !isset($config['service']) && !isset($config['callback']);
    }

    /**
     * @param array  $fieldAttr Field attributes
     * @param object $entity    Entity
     *
     * @return bool
     */
    protected function isFieldContentHidden(array $fieldAttr, $entity): bool
    {
        return !empty($fieldAttr['condition']) && !$this->getExpressionLanguage()->evaluate($fieldAttr['condition'], ['entity' => $entity]);
    }

    /**
     * @return \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    protected function getExpressionLanguage(): ExpressionLanguage
    {
        if (null === $this->expressionLanguage) {
            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }
}
