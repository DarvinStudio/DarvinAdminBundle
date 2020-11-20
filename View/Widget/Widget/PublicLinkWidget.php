<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Callback\CallbackRunnerInterface;
use Darvin\Utils\Homepage\HomepageRouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Public link view widget
 */
class PublicLinkWidget extends AbstractWidget
{
    /**
     * @var \Darvin\Utils\Callback\CallbackRunnerInterface
     */
    private $callbackRunner;

    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Darvin\Utils\Callback\CallbackRunnerInterface $callbackRunner Callback runner
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface $homepageRouter Homepage router
     * @param \Symfony\Component\Routing\RouterInterface     $router         Generic router
     */
    public function __construct(CallbackRunnerInterface $callbackRunner, HomepageRouterInterface $homepageRouter, RouterInterface $router)
    {
        $this->callbackRunner = $callbackRunner;
        $this->homepageRouter = $homepageRouter;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $render = function ($url): ?string {
            $url = trim((string)$url);

            if ('' === $url) {
                return null;
            }

            return sprintf('<a href="%s" target="_blank">%1$s</a>', $url);
        };

        if ($this->homepageRouter->isHomepage($entity)) {
            return $render($this->homepageRouter->generate());
        }
        if (null !== $options['router_service']) {
            return $render($this->callbackRunner->runCallback($options['router_service'], $options['router_method'], $entity));
        }

        $params = [];

        foreach ($options['params'] as $param => $property) {
            if (null === $property) {
                $property = $param;
            }

            $params[$param] = $this->getPropertyValue($entity, $property);
        }

        return $render($this->router->generate($options['route'], $params));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'router_service' => null,
                'router_method'  => null,
                'route'          => 'darvin_content_show',
                'params'         => [
                    'slug' => null,
                ],
            ])
            ->setAllowedTypes('router_service', ['string', 'null'])
            ->setAllowedTypes('router_method', ['string', 'null'])
            ->setAllowedTypes('params', 'array')
            ->setAllowedTypes('route', 'string');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
