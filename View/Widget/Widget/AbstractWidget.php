<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\View\Widget\WidgetInterface;
use Darvin\ContentBundle\Translatable\TranslatableException;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * View widget abstract implementation
 */
abstract class AbstractWidget implements WidgetInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var string|null
     */
    private $alias = null;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver|null
     */
    private $optionsResolver = null;

    /**
     * @var array|null
     */
    private $resolvedOptions = null;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     */
    public function setMetadataManager(AdminMetadataManagerInterface $metadataManager): void
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating Templating
     */
    public function setTemplating(EngineInterface $templating): void
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($entity, array $options = []): ?string
    {
        $this->validateEntity($entity);

        $options = $this->resolveOptions($options);

        foreach ($this->getRequiredPermissions() as $permission) {
            if (!$this->isGranted($permission, $entity)) {
                return null;
            }
        }

        return $this->createContent($entity, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        if (null === $this->alias) {
            $this->alias = StringsUtil::toUnderscore(preg_replace('/^.*\\\|Widget$/', '', get_class($this)));
        }

        return $this->alias;
    }

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return string|null
     */
    abstract protected function createContent($entity, array $options): ?string;

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'property' => null,
                'template' => null,
            ])
            ->setAllowedTypes('property', ['string', 'null'])
            ->setAllowedTypes('template', ['string', 'null']);
    }

    /**
     * @return iterable
     */
    protected function getAllowedEntityClasses(): iterable
    {
        return [];
    }

    /**
     * @return iterable
     */
    protected function getRequiredPermissions(): iterable
    {
        return [];
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        $template = $this->resolvedOptions['template'];

        if (empty($template)) {
            $template = sprintf('@DarvinAdmin/widget/%s.html.twig', $this->getAlias());
        }

        return $template;
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    final protected function getPropertyValue($entity, string $propertyPath)
    {
        try {
            if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
                $message = sprintf('Unable to get value of "%s::$%s" property: it is not readable.', get_class($entity), $propertyPath);

                throw new \InvalidArgumentException($message);
            }
        } catch (TranslatableException $ex) {
            $message = sprintf('Unable to get value of "%s::$%s" property: %s', get_class($entity), $propertyPath, lcfirst($ex->getMessage()));

            throw new \InvalidArgumentException($message);
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @param mixed $attributes Attributes
     * @param mixed $object     Object
     *
     * @return bool
     */
    final protected function isGranted($attributes, $object = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * @param array       $params   Parameters
     * @param string|null $template Template
     *
     * @return string
     */
    final protected function render(array $params = [], ?string $template = null): string
    {
        return $this->templating->render(!empty($template) ? $template : $this->getTemplate(), array_merge($this->resolvedOptions, $params));
    }

    /**
     * @param array $options Options
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function resolveOptions(array $options): array
    {
        if (null === $this->optionsResolver) {
            $resolver = new OptionsResolver();

            $this->configureOptions($resolver);

            $this->optionsResolver = $resolver;
        }
        try {
            $this->resolvedOptions = $this->optionsResolver->resolve($options);
        } catch (ExceptionInterface $ex) {
            throw new \InvalidArgumentException(
                sprintf('View widget "%s" options are invalid: "%s".', $this->getAlias(), $ex->getMessage())
            );
        }

        return $this->resolvedOptions;
    }

    /**
     * @param object $entity Entity
     *
     * @throws \InvalidArgumentException
     */
    private function validateEntity($entity): void
    {
        $allowedClasses = [];

        foreach ($this->getAllowedEntityClasses() as $class) {
            if ($entity instanceof $class) {
                return;
            }

            $allowedClasses[] = $class;
        }
        if (!empty($allowedClasses)) {
            $message = sprintf(
                'View widget "%s" requires entity to be instance of one of "%s" classes.',
                $this->getAlias(),
                implode('", "', $allowedClasses)
            );

            throw new \InvalidArgumentException($message);
        }
    }
}
