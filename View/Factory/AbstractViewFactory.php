<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Darvin\Utils\Strings\Stringifier\DoctrineStringifierInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * View factory abstract implementation
 */
abstract class AbstractViewFactory
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Darvin\Utils\Strings\Stringifier\DoctrineStringifierInterface
     */
    protected $stringifier;

    /**
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    protected $widgetPool;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Psr\Container\ContainerInterface $container DI container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param \Darvin\Utils\Strings\Stringifier\DoctrineStringifierInterface $stringifier Doctrine stringifier
     */
    public function setStringifier(DoctrineStringifierInterface $stringifier): void
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
    protected function getFieldContent(object $entity, string $fieldName, array $fieldAttr, array $mappings)
    {
        if (!empty($fieldAttr['widget'])) {
            $widgetAlias = key($fieldAttr['widget']);

            return $this->widgetPool->getWidget($widgetAlias)->getContent($entity, array_merge([
                'property' => $fieldName,
            ], $fieldAttr['widget'][$widgetAlias]));
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
            ? $this->stringifier->stringify($content, (string)$mappings[$fieldName]['type'])
            : $content;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param object                                $entity   Entity
     * @param string                                $viewType View type
     *
     * @throws \RuntimeException
     */
    protected function validateConfiguration(Metadata $meta, object $entity, string $viewType): void
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
    protected function isFieldContentHidden(array $fieldAttr, object $entity): bool
    {
        return null !== $fieldAttr['condition']
            && !$this->authorizationChecker->isGranted(new Expression($fieldAttr['condition']), $entity);
    }
}
