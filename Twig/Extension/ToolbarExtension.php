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

use Darvin\AdminBundle\Toolbar\ToolbarRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Toolbar Twig extension
 */
class ToolbarExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Toolbar\ToolbarRendererInterface
     */
    private $toolbarRenderer;

    /**
     * @param \Darvin\AdminBundle\Toolbar\ToolbarRendererInterface $toolbarRenderer Toolbar renderer
     */
    public function __construct(ToolbarRendererInterface $toolbarRenderer)
    {
        $this->toolbarRenderer = $toolbarRenderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('admin_toolbar', [$this->toolbarRenderer, 'renderToolbar'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
