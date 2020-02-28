<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\Toolbar;

use Twig\Environment;

/**
 * Toolbar renderer
 */
class ToolbarRenderer implements ToolbarRendererInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Twig\Environment $twig Twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderToolbar(): ?string
    {
        return $this->twig->render('@DarvinAdmin/toolbar.html.twig');
    }
}
