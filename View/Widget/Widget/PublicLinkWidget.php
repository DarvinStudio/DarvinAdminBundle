<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Homepage\HomepageRouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
     * @param \Symfony\Component\Routing\RouterInterface     $router         Generic router
     */
    public function __construct(HomepageRouterInterface $homepageRouter, RouterInterface $router)
    {
        $this->homepageRouter = $homepageRouter;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        if ($this->homepageRouter->isHomepage($entity)) {
            return $this->render([
                'url' => $this->homepageRouter->generate(),
            ]);
        }

        $params = [];

        foreach ($options['params'] as $param => $property) {
            if (empty($property)) {
                $property = $param;
            }

            $params[$param] = $this->getPropertyValue($entity, $property);
        }

        return $this->render([
            'url' => $this->router->generate($options['route'], $params),
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
