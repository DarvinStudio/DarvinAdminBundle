<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Public link view widget generator
 */
class PublicLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router Router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, array $options)
    {
        $route = $options['route'];

        if (null === $this->router->getRouteCollection()->get($route)) {
            throw new WidgetGeneratorException(sprintf('Route "%s" does not exist.', $route));
        }

        $parameters = array();

        foreach ($options['parameters'] as $paramName => $propertyPath) {
            if (empty($propertyPath)) {
                $propertyPath = $paramName;
            }
            if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
                throw new WidgetGeneratorException(
                    sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $propertyPath)
                );
            }

            $parameters[$paramName] = $this->propertyAccessor->getValue($entity, $propertyPath);
        }
        try {
            $url = $this->router->generate($route, $parameters);
        } catch (ExceptionInterface $ex) {
            throw new WidgetGeneratorException($ex->getMessage());
        }

        return $this->render($options, array(
            'url' => $url,
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(array(
                'parameters',
                'route',
            ))
            ->setAllowedTypes('parameters', 'array')
            ->setAllowedTypes('route', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return array(
            Permission::VIEW,
        );
    }
}
