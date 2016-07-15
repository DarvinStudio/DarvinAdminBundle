<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

/**
 * Menu Twig extension
 */
class MenuExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'admin_menu',
                [$this, 'renderMenu'],
                [
                    'is_safe'           => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    /**
     * @param \Twig_Environment $environment Twig environment
     * @param string            $template    Template
     *
     * @return string
     */
    public function renderMenu(\Twig_Environment $environment, $template = 'DarvinAdminBundle::menu.html.twig')
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_menu_extension';
    }
}
