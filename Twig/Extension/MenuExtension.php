<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Menu\MenuInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Menu Twig extension
 */
class MenuExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Menu\MenuInterface
     */
    private $menu;

    /**
     * @param \Darvin\AdminBundle\Menu\MenuInterface $menu Menu
     */
    public function __construct(MenuInterface $menu)
    {
        $this->menu = $menu;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_menu', [$this, 'renderMenu'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * @param \Twig\Environment $environment Twig environment
     * @param string            $template    Template
     *
     * @return string
     */
    public function renderMenu(Environment $environment, string $template = '@DarvinAdmin/menu.html.twig'): string
    {
        return $environment->render($template, [
            'items' => $this->menu->getItems(),
        ]);
    }
}
