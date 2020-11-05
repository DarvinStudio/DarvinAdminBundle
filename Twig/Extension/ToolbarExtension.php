<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\Utils\Service\ServiceProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Toolbar Twig extension
 */
class ToolbarExtension extends AbstractExtension
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $toolbarRendererProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $toolbarRendererProvider Toolbar renderer service provider
     */
    public function __construct(ServiceProviderInterface $toolbarRendererProvider)
    {
        $this->toolbarRendererProvider = $toolbarRendererProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('admin_toolbar', [$this->getToolbarRenderer(), 'renderToolbar'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return \Darvin\AdminBundle\Toolbar\ToolbarRendererInterface
     */
    private function getToolbarRenderer()
    {
        return $this->toolbarRendererProvider->getService();
    }
}
