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
use Darvin\AdminBundle\View\Widget\WidgetException;
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
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected $optionsResolver;

    /**
     * @var string|null
     */
    protected $alias;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface                   $metadataManager      Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface                  $propertyAccessor     Property accessor
     * @param \Symfony\Component\Templating\EngineInterface                                $templating           Templating
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AdminMetadataManagerInterface $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        EngineInterface $templating
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->templating = $templating;
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);

        $this->alias = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($entity, array $options = []): ?string
    {
        $options = $this->validate($entity, $options);

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
            $parts = explode('\\', get_class($this));

            $this->alias = StringsUtil::toUnderscore(preg_replace('/Widget$/', '', array_pop($parts)));
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
        $resolver->setDefault('property', null);
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate(): string
    {
        return sprintf('@DarvinAdmin/widget/%s.html.twig', $this->getAlias());
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     *
     * @return mixed
     * @throws \Darvin\AdminBundle\View\Widget\WidgetException
     */
    protected function getPropertyValue($entity, string $propertyPath)
    {
        try {
            if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
                $message = sprintf('Unable to get value of "%s::$%s" property: it is not readable.', get_class($entity), $propertyPath);

                throw new WidgetException($message);
            }
        } catch (TranslatableException $ex) {
            $message = sprintf('Unable to get value of "%s::$%s" property: %s', get_class($entity), $propertyPath, lcfirst($ex->getMessage()));

            throw new WidgetException($message);
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @return array
     */
    protected function getAllowedEntityClasses(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getRequiredPermissions(): array
    {
        return [];
    }

    /**
     * @param mixed $attributes Attributes
     * @param mixed $object     Object
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * @param array $options        Options
     * @param array $templateParams Template parameters
     *
     * @return string
     */
    protected function render(array $options, array $templateParams = []): string
    {
        $template = isset($options['template']) ? $options['template'] : $this->getDefaultTemplate();

        return $this->templating->render($template, array_merge($options, $templateParams));
    }

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return array
     * @throws \Darvin\AdminBundle\View\Widget\WidgetException
     */
    protected function validate($entity, array $options): array
    {
        $allowedEntityClasses = $this->getAllowedEntityClasses();

        if (!empty($allowedEntityClasses)) {
            $entityClassAllowed = false;

            foreach ($allowedEntityClasses as $allowedEntityClass) {
                if ($entity instanceof $allowedEntityClass) {
                    $entityClassAllowed = true;

                    break;
                }
            }
            if (!$entityClassAllowed) {
                $message = sprintf(
                    'View widget "%s" requires entity to be instance of one of "%s" classes.',
                    $this->getAlias(),
                    implode('", "', $allowedEntityClasses)
                );

                throw new WidgetException($message);
            }
        }
        try {
            $options = $this->optionsResolver->resolve($options);
        } catch (ExceptionInterface $ex) {
            throw new WidgetException(
                sprintf('View widget "%s" options are invalid: "%s".', $this->getAlias(), $ex->getMessage())
            );
        }

        return $options;
    }
}
