<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
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
    protected function createContent($entity, array $options): ?string
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
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'route'  => 'darvin_content_show',
                'params' => [
                    'slug' => null,
                ],
            ])
            ->setAllowedTypes('params', 'array')
            ->setAllowedTypes('route', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
