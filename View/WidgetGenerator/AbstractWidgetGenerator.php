<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\ContentBundle\Translatable\TranslatableException;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * View widget generator abstract implementation
 */
abstract class AbstractWidgetGenerator implements WidgetGeneratorInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private $optionsResolver;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                                 $metadataManager      Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface                  $propertyAccessor     Property accessor
     * @param \Symfony\Component\Templating\EngineInterface                                $templating           Templating
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        EngineInterface $templating
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->templating = $templating;
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        $this->validate($entity, $options);

        foreach ($this->getRequiredPermissions() as $permission) {
            if (!$this->isGranted($permission, $entity)) {
                return '';
            }
        }

        return $this->generateWidget($entity, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        $parts = explode('\\', get_class($this));
        $alias = StringsUtil::toUnderscore(preg_replace('/Generator$/', '', array_pop($parts)));

        return $alias;
    }

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @return string
     */
    abstract protected function generateWidget($entity, array $options);

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return sprintf('DarvinAdminBundle:Widget:%s.html.twig', $this->getAlias());
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     *
     * @return mixed
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    protected function getPropertyValue($entity, $propertyPath)
    {
        try {
            if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
                throw new WidgetGeneratorException(
                    sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $propertyPath)
                );
            }
        } catch (TranslatableException $ex) {
            throw new WidgetGeneratorException($ex->getMessage());
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @param string $permission Permission
     * @param object $entity     Entity
     *
     * @return bool
     */
    protected function isGranted($permission, $entity)
    {
        return $this->authorizationChecker->isGranted($permission, $entity);
    }

    /**
     * @param array $options        Options
     * @param array $templateParams Template parameters
     *
     * @return string
     */
    protected function render(array $options, array $templateParams = array())
    {
        $template = isset($options['template']) ? $options['template'] : $this->getDefaultTemplate();

        return $this->templating->render($template, $templateParams);
    }

    /**
     * @return string
     */
    protected function getRequiredEntityClass()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getRequiredPermissions()
    {
        return array();
    }

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    private function validate($entity, array &$options)
    {
        $requiredEntityClass = $this->getRequiredEntityClass();

        if (!empty($requiredEntityClass) && !$entity instanceof $requiredEntityClass) {
            $message = sprintf(
                'View widget generator "%s" requires entity to be instance of "%s".',
                $this->getAlias(),
                $requiredEntityClass
            );

            throw new WidgetGeneratorException($message);
        }
        try {
            $options = $this->optionsResolver->resolve($options);
        } catch (ExceptionInterface $ex) {
            throw new WidgetGeneratorException(
                sprintf('View widget generator "%s" options are invalid: "%s".', $this->getAlias(), $ex->getMessage())
            );
        }
    }
}
