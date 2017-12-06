<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\WidgetException;
use Darvin\Utils\Homepage\HomepageRouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Public link view widget
 */
class PublicLinkWidget extends AbstractWidget
{
    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface $homepageRouter Homepage router
     */
    public function setHomepageRouter(HomepageRouterInterface $homepageRouter)
    {
        $this->homepageRouter = $homepageRouter;
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
    protected function createContent($entity, array $options, $property)
    {
        if ($this->homepageRouter->isHomepage($entity)) {
            try {
                return $this->render($options, [
                    'url' => $this->homepageRouter->generate(),
                ]);
            } catch (ExceptionInterface $ex) {
                throw new WidgetException($ex->getMessage());
            }
        }

        $parameters = [];

        foreach ($options['params'] as $paramName => $propertyPath) {
            if (empty($propertyPath)) {
                $propertyPath = $paramName;
            }

            $parameters[$paramName] = $this->getPropertyValue($entity, $propertyPath);
        }
        try {
            $url = $this->router->generate($options['route'], $parameters);
        } catch (ExceptionInterface $ex) {
            throw new WidgetException($ex->getMessage());
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
