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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Public link view widget generator
 */
class PublicLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

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
    protected function generateWidget($entity, array $options, $property)
    {
        $route = $options['route'];

        if (null === $this->router->getRouteCollection()->get($route)) {
            throw new WidgetGeneratorException(sprintf('Route "%s" does not exist.', $route));
        }

        $parameters = [];

        foreach ($options['params'] as $paramName => $propertyPath) {
            if (empty($propertyPath)) {
                $propertyPath = $paramName;
            }

            $parameters[$paramName] = $this->getPropertyValue($entity, $propertyPath);
        }
        try {
            $url = $this->router->generate($route, $parameters);
        } catch (ExceptionInterface $ex) {
            throw new WidgetGeneratorException($ex->getMessage());
        }

        return $this->render($options, [
            'url' => $url,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired([
                'params',
                'route',
            ])
            ->setAllowedTypes('params', 'array')
            ->setAllowedTypes('route', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
